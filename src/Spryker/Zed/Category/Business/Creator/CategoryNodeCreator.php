<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Creator;

use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Spryker\Zed\Category\Business\Model\CategoryToucherInterface;
use Spryker\Zed\Category\Business\Publisher\CategoryNodePublisherInterface;
use Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class CategoryNodeCreator implements CategoryNodeCreatorInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface
     */
    protected $categoryEntityManager;

    /**
     * @var \Spryker\Zed\Category\Business\Publisher\CategoryNodePublisherInterface
     */
    protected $categoryNodePublisher;

    /**
     * @var \Spryker\Zed\Category\Business\Creator\CategoryClosureTableCreatorInterface
     */
    protected $categoryClosureTableCreator;

    /**
     * @var \Spryker\Zed\Category\Business\Creator\CategoryUrlCreatorInterface
     */
    protected $categoryUrlCreator;

    /**
     * @var \Spryker\Zed\Category\Business\Model\CategoryToucherInterface
     */
    protected $categoryToucher;

    /**
     * @param \Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface $categoryEntityManager
     * @param \Spryker\Zed\Category\Business\Publisher\CategoryNodePublisherInterface $categoryNodePublisher
     * @param \Spryker\Zed\Category\Business\Creator\CategoryClosureTableCreatorInterface $categoryClosureTableCreator
     * @param \Spryker\Zed\Category\Business\Creator\CategoryUrlCreatorInterface $categoryUrlCreator
     * @param \Spryker\Zed\Category\Business\Model\CategoryToucherInterface $categoryToucher
     */
    public function __construct(
        CategoryEntityManagerInterface $categoryEntityManager,
        CategoryNodePublisherInterface $categoryNodePublisher,
        CategoryClosureTableCreatorInterface $categoryClosureTableCreator,
        CategoryUrlCreatorInterface $categoryUrlCreator,
        CategoryToucherInterface $categoryToucher
    ) {
        $this->categoryEntityManager = $categoryEntityManager;
        $this->categoryNodePublisher = $categoryNodePublisher;
        $this->categoryClosureTableCreator = $categoryClosureTableCreator;
        $this->categoryUrlCreator = $categoryUrlCreator;
        $this->categoryToucher = $categoryToucher;
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    public function createCategoryNode(CategoryTransfer $categoryTransfer): void
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($categoryTransfer) {
            $this->executeCreateCategoryNodeTransaction($categoryTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    public function createExtraParentsCategoryNodes(CategoryTransfer $categoryTransfer): void
    {
        if ($categoryTransfer->getExtraParents()->count() === 0) {
            return;
        }

        $this->getTransactionHandler()->handleTransaction(function () use ($categoryTransfer) {
            $this->executeCreateExtraParentsCategoryNodesTransaction($categoryTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param \Generated\Shared\Transfer\NodeTransfer $extraParentNodeTransfer
     *
     * @return void
     */
    public function addExtraParentCategoryNodeToCategory(CategoryTransfer $categoryTransfer, NodeTransfer $extraParentNodeTransfer): void
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($categoryTransfer, $extraParentNodeTransfer) {
            $this->assignExtraParent($categoryTransfer, $extraParentNodeTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    protected function executeCreateCategoryNodeTransaction(CategoryTransfer $categoryTransfer): void
    {
        $nodeTransfer = $categoryTransfer->getCategoryNodeOrFail()
            ->setIsMain(true)
            ->setFkCategory($categoryTransfer->getIdCategory());

        $nodeTransfer = $this->setParentCategoryNode($nodeTransfer, $categoryTransfer);

        $nodeTransfer = $this->categoryEntityManager->createCategoryNode($nodeTransfer);

        $this->categoryClosureTableCreator->createCategoryClosureTable($nodeTransfer);

        if ($categoryTransfer->getIsActive()) {
            $this->categoryToucher->touchCategoryNodeActiveRecursively($nodeTransfer->getIdCategoryNodeOrFail());
        }

        $this->categoryNodePublisher->triggerBulkCategoryNodePublishEventForCreate($nodeTransfer->getIdCategoryNodeOrFail());
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    protected function executeCreateExtraParentsCategoryNodesTransaction(CategoryTransfer $categoryTransfer): void
    {
        foreach ($categoryTransfer->getExtraParents() as $extraParentNodeTransfer) {
            $this->assignExtraParent($categoryTransfer, $extraParentNodeTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param \Generated\Shared\Transfer\NodeTransfer $extraParentNodeTransfer
     *
     * @return void
     */
    protected function assignExtraParent(CategoryTransfer $categoryTransfer, NodeTransfer $extraParentNodeTransfer): void
    {
        $nodeTransfer = $this->categoryEntityManager->saveCategoryExtraParentNode($categoryTransfer, $extraParentNodeTransfer);

        $this->categoryClosureTableCreator->createCategoryClosureTable($nodeTransfer);
        $this->categoryUrlCreator->createLocalizedCategoryUrlsForNode($nodeTransfer, $categoryTransfer->getLocalizedAttributes());

        $this->categoryToucher->touchCategoryNodeActiveRecursively($nodeTransfer->getIdCategoryNodeOrFail());
    }

    /**
     * @param \Generated\Shared\Transfer\NodeTransfer $nodeTransfer
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return \Generated\Shared\Transfer\NodeTransfer
     */
    protected function setParentCategoryNode(NodeTransfer $nodeTransfer, CategoryTransfer $categoryTransfer): NodeTransfer
    {
        $parentCategoryNode = $categoryTransfer->getParentCategoryNode();
        if ($parentCategoryNode !== null) {
            return $nodeTransfer->setFkParentCategoryNode($parentCategoryNode->getIdCategoryNode());
        }

        return $nodeTransfer
            ->setIsRoot(true)
            ->setFkParentCategoryNode(null);
    }
}
