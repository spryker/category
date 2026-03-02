<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Updater;

use ArrayObject;
use Generated\Shared\Transfer\CategoryNodeCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeCollectionTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Spryker\Zed\Category\Business\Creator\CategoryNodeCreatorInterface;
use Spryker\Zed\Category\Business\Deleter\CategoryNodeDeleterInterface;
use Spryker\Zed\Category\Business\Model\CategoryToucherInterface;
use Spryker\Zed\Category\Business\Publisher\CategoryNodePublisherInterface;
use Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface;
use Spryker\Zed\Category\Persistence\CategoryRepositoryInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class CategoryNodeUpdater implements CategoryNodeUpdaterInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface
     */
    protected $categoryEntityManager;

    /**
     * @var \Spryker\Zed\Category\Business\Updater\CategoryClosureTableUpdaterInterface
     */
    protected $categoryClosureTableUpdater;

    /**
     * @var \Spryker\Zed\Category\Business\Model\CategoryToucherInterface
     */
    protected $categoryToucher;

    /**
     * @var \Spryker\Zed\Category\Business\Publisher\CategoryNodePublisherInterface
     */
    protected $categoryNodePublisher;

    /**
     * @var \Spryker\Zed\Category\Business\Deleter\CategoryNodeDeleterInterface
     */
    protected $categoryNodeDeleter;

    /**
     * @var \Spryker\Zed\Category\Business\Creator\CategoryNodeCreatorInterface
     */
    protected $categoryNodeCreator;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryEntityManagerInterface $categoryEntityManager,
        CategoryClosureTableUpdaterInterface $categoryClosureTableUpdater,
        CategoryToucherInterface $categoryToucher,
        CategoryNodePublisherInterface $categoryNodePublisher,
        CategoryNodeDeleterInterface $categoryNodeDeleter,
        CategoryNodeCreatorInterface $categoryNodeCreator
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryEntityManager = $categoryEntityManager;
        $this->categoryClosureTableUpdater = $categoryClosureTableUpdater;
        $this->categoryToucher = $categoryToucher;
        $this->categoryNodePublisher = $categoryNodePublisher;
        $this->categoryNodeDeleter = $categoryNodeDeleter;
        $this->categoryNodeCreator = $categoryNodeCreator;
    }

    public function updateCategoryNode(CategoryTransfer $categoryTransfer): void
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($categoryTransfer) {
            $this->executeUpdateCategoryNodeTransaction($categoryTransfer);
        });
    }

    public function updateExtraParentCategoryNodes(CategoryTransfer $categoryTransfer): void
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($categoryTransfer) {
            $this->executeUpdateExtraParentCategoryNodesTransaction($categoryTransfer);
        });
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\Category\Business\Reorderer\CategoryNodeReorderer::reorderCategoryNodeCollection()} instead.
     *
     * @param int $idCategoryNode
     * @param int $position
     *
     * @return void
     */
    public function updateCategoryNodeOrder(int $idCategoryNode, int $position): void
    {
        $nodeTransfer = (new NodeTransfer())
            ->setIdCategoryNode($idCategoryNode)
            ->setNodeOrder($position);

        $this->categoryEntityManager->updateCategoryNode($nodeTransfer);
    }

    protected function executeUpdateCategoryNodeTransaction(CategoryTransfer $categoryTransfer): void
    {
        $nodeTransfer = $categoryTransfer->getCategoryNodeOrFail();
        $currentCategoryNodeTransfer = $this->categoryRepository->findCategoryNodeByIdCategoryNode(
            $nodeTransfer->getIdCategoryNodeOrFail(),
        );

        if (!$currentCategoryNodeTransfer) {
            return;
        }

        $idFormerParentCategoryNode = $this->findPossibleFormerParentCategoryNodeId(
            $currentCategoryNodeTransfer,
            $categoryTransfer,
        );
        $idParentCategoryNode = $categoryTransfer->getParentCategoryNode()
            ? $categoryTransfer->getParentCategoryNodeOrFail()->getIdCategoryNode()
            : null;

        if ($idParentCategoryNode !== $idFormerParentCategoryNode) {
            $nodeTransfer->setFkParentCategoryNode($idParentCategoryNode);
        }

        $nodeTransfer = $this->categoryEntityManager->updateCategoryNode($nodeTransfer);
        $this->categoryClosureTableUpdater->updateCategoryClosureTableParentEntriesForCategoryNode($nodeTransfer);
        $categoryTransfer->setCategoryNode($nodeTransfer);

        $this->touchCategoryNode($categoryTransfer, $nodeTransfer);
        $this->touchPossibleFormerParentCategoryNode($idFormerParentCategoryNode);

        $this->categoryNodePublisher->triggerBulkCategoryNodePublishEventForUpdate($nodeTransfer->getIdCategoryNodeOrFail());
    }

    protected function executeUpdateExtraParentCategoryNodesTransaction(CategoryTransfer $categoryTransfer): void
    {
        $categoryNodeCriteriaTransfer = (new CategoryNodeCriteriaTransfer())
            ->addIdCategory($categoryTransfer->getIdCategoryOrFail())
            ->setIsMain(false);

        $existingExtraParentCategoryNodeTransferCollection = $this->categoryRepository
            ->getCategoryNodes($categoryNodeCriteriaTransfer);

        $newExtraParentCategoryNodeIds = $this->getCategoryNodeIdsFromNodeTransfers($categoryTransfer->getExtraParents());
        $existingExtraParentCategoryNodeIds = $this->getCategoryNodeIdsFromNodeTransfers($existingExtraParentCategoryNodeTransferCollection->getNodes());

        if ($newExtraParentCategoryNodeIds === [] && $existingExtraParentCategoryNodeIds === []) {
            return;
        }

        $extraParentCategoryNodeIdsToAdd = array_diff($newExtraParentCategoryNodeIds, $existingExtraParentCategoryNodeIds);
        $extraParentCategoryNodeIdsToDelete = array_diff($existingExtraParentCategoryNodeIds, $newExtraParentCategoryNodeIds);

        $idParentCategoryNode = $categoryTransfer->getParentCategoryNodeOrFail()->getIdCategoryNodeOrFail();
        foreach ($categoryTransfer->getExtraParents() as $extraParentNodeTransfer) {
            if ($idParentCategoryNode === $extraParentNodeTransfer->getIdCategoryNode()) {
                continue;
            }

            $this->updateExistingExtraParentCategoryNode($existingExtraParentCategoryNodeTransferCollection, $extraParentNodeTransfer);
            $this->assignNewExtraParentNode($extraParentCategoryNodeIdsToAdd, $extraParentNodeTransfer, $categoryTransfer);
        }

        $this->removeDeassignedExtraParents($existingExtraParentCategoryNodeTransferCollection, $extraParentCategoryNodeIdsToDelete);
    }

    protected function findPossibleFormerParentCategoryNodeId(
        NodeTransfer $currentCategoryNodeTransfer,
        CategoryTransfer $categoryTransfer
    ): ?int {
        if ($categoryTransfer->getCategoryNodeOrFail()->getIsRoot()) {
            return null;
        }

        if (!$categoryTransfer->getParentCategoryNode()) {
            return null;
        }

        $parentCategoryNodeTransfer = $categoryTransfer->getParentCategoryNodeOrFail();
        $idFormerParentCategoryNode = $currentCategoryNodeTransfer->getFkParentCategoryNode();

        if ($parentCategoryNodeTransfer->getIdCategoryNode() !== $idFormerParentCategoryNode) {
            return $idFormerParentCategoryNode;
        }

        return null;
    }

    protected function touchCategoryNode(CategoryTransfer $categoryTransfer, NodeTransfer $categoryNodeTransfer): void
    {
        $idCategoryNode = $categoryNodeTransfer->getIdCategoryNodeOrFail();

        if ($categoryTransfer->getIsActive()) {
            $this->categoryToucher->touchCategoryNodeActiveRecursively($idCategoryNode);

            return;
        }

        $this->categoryToucher->touchCategoryNodeDeletedRecursively($idCategoryNode);
    }

    protected function touchPossibleFormerParentCategoryNode(?int $idCategoryNode): void
    {
        if (!$idCategoryNode) {
            return;
        }

        $this->categoryToucher->touchFormerParentCategoryNodeActiveRecursively($idCategoryNode);
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\NodeTransfer> $nodeTransfers
     *
     * @return array<int>
     */
    protected function getCategoryNodeIdsFromNodeTransfers(ArrayObject $nodeTransfers): array
    {
        return array_map(function (NodeTransfer $nodeTransfer) {
            return $nodeTransfer->getIdCategoryNodeOrFail();
        }, $nodeTransfers->getArrayCopy());
    }

    protected function updateExistingExtraParentCategoryNode(
        NodeCollectionTransfer $existingExtraParentNodeTransferCollection,
        NodeTransfer $newExtraParentNodeTransfer
    ): void {
        foreach ($existingExtraParentNodeTransferCollection->getNodes() as $existingCategoryExtraNodeTransfer) {
            if ($existingCategoryExtraNodeTransfer->getFkParentCategoryNodeOrFail() !== $newExtraParentNodeTransfer->getIdCategoryNode()) {
                continue;
            }

            $this->categoryClosureTableUpdater->updateCategoryClosureTableParentEntriesForCategoryNode($existingCategoryExtraNodeTransfer);
        }
    }

    /**
     * @param array<int> $extraParentCategoryNodeIdsToAdd
     * @param \Generated\Shared\Transfer\NodeTransfer $newExtraParentNodeTransfer
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    protected function assignNewExtraParentNode(
        array $extraParentCategoryNodeIdsToAdd,
        NodeTransfer $newExtraParentNodeTransfer,
        CategoryTransfer $categoryTransfer
    ): void {
        if (!in_array($newExtraParentNodeTransfer->getIdCategoryNode(), $extraParentCategoryNodeIdsToAdd, true)) {
            return;
        }

        $this->categoryNodeCreator->addExtraParentCategoryNodeToCategory($categoryTransfer, $newExtraParentNodeTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\NodeCollectionTransfer $existingExtraParentNodeCollection
     * @param array<int> $extraParentNodeIdsToDelete
     *
     * @return void
     */
    protected function removeDeassignedExtraParents(NodeCollectionTransfer $existingExtraParentNodeCollection, array $extraParentNodeIdsToDelete): void
    {
        $nodeTransfersToDelete = [];
        foreach ($existingExtraParentNodeCollection->getNodes() as $nodeTransfer) {
            if (!in_array($nodeTransfer->getIdCategoryNodeOrFail(), $extraParentNodeIdsToDelete, true)) {
                continue;
            }

            $nodeTransfersToDelete[] = $nodeTransfer;
        }

        $this->categoryNodeDeleter->deleteCategoryNodes($nodeTransfersToDelete);
    }
}
