<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Updater;

use ArrayObject;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer;
use Generated\Shared\Transfer\CategoryNodeUrlCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use Spryker\Zed\Category\Business\Creator\CategoryUrlCreatorInterface;
use Spryker\Zed\Category\Business\Generator\UrlPathGeneratorInterface;
use Spryker\Zed\Category\CategoryConfig;
use Spryker\Zed\Category\Dependency\Facade\CategoryToUrlInterface;
use Spryker\Zed\Category\Persistence\CategoryRepositoryInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class CategoryUrlUpdater implements CategoryUrlUpdaterInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Spryker\Zed\Category\Business\Generator\UrlPathGeneratorInterface
     */
    protected $urlPathGenerator;

    /**
     * @var \Spryker\Zed\Category\Dependency\Facade\CategoryToUrlInterface
     */
    protected $urlFacade;

    /**
     * @var \Spryker\Zed\Category\CategoryConfig;
     */
    protected $categoryConfig;

    /**
     * @var \Spryker\Zed\Category\Business\Creator\CategoryUrlCreatorInterface
     */
    protected $categoryUrlCreator;

    /**
     * @param \Spryker\Zed\Category\Persistence\CategoryRepositoryInterface $categoryRepository
     * @param \Spryker\Zed\Category\Business\Generator\UrlPathGeneratorInterface $urlPathGenerator
     * @param \Spryker\Zed\Category\Dependency\Facade\CategoryToUrlInterface $urlFacade
     * @param \Spryker\Zed\Category\CategoryConfig $categoryConfig
     * @param \Spryker\Zed\Category\Business\Creator\CategoryUrlCreatorInterface $categoryUrlCreator
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        UrlPathGeneratorInterface $urlPathGenerator,
        CategoryToUrlInterface $urlFacade,
        CategoryConfig $categoryConfig,
        CategoryUrlCreatorInterface $categoryUrlCreator
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->urlPathGenerator = $urlPathGenerator;
        $this->urlFacade = $urlFacade;
        $this->categoryConfig = $categoryConfig;
        $this->categoryUrlCreator = $categoryUrlCreator;
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    public function updateCategoryUrl(CategoryTransfer $categoryTransfer): void
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($categoryTransfer) {
            $this->executeUpdateCategoryUrlTransaction($categoryTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    protected function executeUpdateCategoryUrlTransaction(CategoryTransfer $categoryTransfer): void
    {
        $categoryNodeChildCount = $this->categoryRepository->getCategoryNodeChildCountByParentNodeId($categoryTransfer);

        if (!$categoryNodeChildCount) {
            $this->updateCategoryNodeUrls($categoryTransfer);

            return;
        }

        $this->bulkUpdateCategoryNodeUrls($categoryTransfer, $categoryNodeChildCount);
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    public function updateCategoryNodeUrls(CategoryTransfer $categoryTransfer): void
    {
        $nodeTransfer = $categoryTransfer->getCategoryNodeOrFail();

        $categoryNodeUrlCriteriaTransfer = (new CategoryNodeUrlCriteriaTransfer())
            ->addIdCategoryNode($nodeTransfer->getIdCategoryNodeOrFail());

        $urlTransfers = $this->categoryRepository->getCategoryNodeUrls($categoryNodeUrlCriteriaTransfer);

        foreach ($categoryTransfer->getLocalizedAttributes() as $categoryLocalizedAttributesTransfer) {
            $this->updateCategoryNodeUrlsForLocale(
                $nodeTransfer,
                $urlTransfers,
                $categoryLocalizedAttributesTransfer->getLocaleOrFail(),
            );
        }

        $this->createCategoryNodeUrlsForNewLocalizedAttributes($categoryTransfer, $urlTransfers, $nodeTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param int $categoryNodeChildCount
     *
     * @return void
     */
    protected function bulkUpdateCategoryNodeUrls(CategoryTransfer $categoryTransfer, int $categoryNodeChildCount): void
    {
        $categoryNodeUrlCriteriaTransfer = new CategoryNodeUrlCriteriaTransfer();
        $categoryReadChunkSize = $this->categoryConfig->getCategoryReadChunkSize();

        $categoryCriteriaTransfer = (new CategoryCriteriaTransfer())
            ->setPagination(
                (new PaginationTransfer())->setLimit($categoryReadChunkSize),
            );

        for ($offset = 0; $offset <= $categoryNodeChildCount; $offset += $categoryReadChunkSize) {
            $categoryCriteriaTransfer->getPaginationOrFail()->setOffset($offset);

            $categoryNodeIds = $this->categoryRepository
                ->getDescendantCategoryNodeIdsByIdCategory($categoryTransfer, $categoryCriteriaTransfer);
            $categoryNodeIds[] = $categoryTransfer->getCategoryNodeOrFail()->getIdCategoryNodeOrFail();

            $categoryNodeUrlCriteriaTransfer->setCategoryNodeIds($categoryNodeIds);
            $urlTransfers = $this->categoryRepository->getCategoryNodeUrls($categoryNodeUrlCriteriaTransfer);

            $this->bulkUpdateCategoryNodeUrlsForLocale($categoryNodeIds, $urlTransfers, $categoryTransfer);
        }

        $categoryNodeUrlCriteriaTransfer = (new CategoryNodeUrlCriteriaTransfer())
            ->addIdCategoryNode($categoryTransfer->getIdCategoryOrFail());
        $urlTransfers = $this->categoryRepository->getCategoryNodeUrls($categoryNodeUrlCriteriaTransfer);

        $this->createCategoryNodeUrlsForNewLocalizedAttributes($categoryTransfer, $urlTransfers, $categoryTransfer->getCategoryNodeOrFail());
    }

    /**
     * @param array<int> $categoryNodeIds
     * @param array<\Generated\Shared\Transfer\UrlTransfer> $urlTransfers
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    protected function bulkUpdateCategoryNodeUrlsForLocale(
        array $categoryNodeIds,
        array $urlTransfers,
        CategoryTransfer $categoryTransfer
    ): void {
        foreach ($categoryTransfer->getLocalizedAttributes() as $categoryLocalizedAttributesTransfer) {
            $localeTransfer = $categoryLocalizedAttributesTransfer->getLocaleOrFail();

            $indexedCategoryUrlPaths = $this->urlPathGenerator->bulkBuildCategoryNodeUrlForLocale(
                $categoryNodeIds,
                $categoryLocalizedAttributesTransfer->getLocaleOrFail(),
            );

            $this->updateNodeUrls($urlTransfers, $localeTransfer, $indexedCategoryUrlPaths);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\NodeTransfer $nodeTransfer
     * @param array<\Generated\Shared\Transfer\UrlTransfer> $urlTransfers
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return void
     */
    protected function updateCategoryNodeUrlsForLocale(
        NodeTransfer $nodeTransfer,
        array $urlTransfers,
        LocaleTransfer $localeTransfer
    ): void {
        foreach ($urlTransfers as $urlTransfer) {
            if (
                !$this->checkUrlLocale($urlTransfer, $localeTransfer)
                || $urlTransfer->getFkResourceCategorynodeOrFail() != $nodeTransfer->getIdCategoryNodeOrFail()
            ) {
                continue;
            }

            $urlPath = $this->urlPathGenerator->buildCategoryNodeUrlForLocale($nodeTransfer, $localeTransfer);
            if ($urlPath === $urlTransfer->getUrl()) {
                continue;
            }

            $urlTransfer->setUrl($urlPath);
            $this->urlFacade->updateUrl($urlTransfer);
        }
    }

    /**
     * @param array<\Generated\Shared\Transfer\UrlTransfer> $urlTransfers
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     * @param array $indexedCategoryUrlPaths
     *
     * @return void
     */
    protected function updateNodeUrls(
        array $urlTransfers,
        LocaleTransfer $localeTransfer,
        array $indexedCategoryUrlPaths
    ): void {
        foreach ($urlTransfers as $urlTransfer) {
            if (!$this->checkUrlLocale($urlTransfer, $localeTransfer)) {
                continue;
            }

            $categoryUrlPath = $indexedCategoryUrlPaths[$urlTransfer->getFkResourceCategorynodeOrFail()] ?? null;

            if (!$categoryUrlPath || $categoryUrlPath === $urlTransfer->getUrl()) {
                continue;
            }

            $urlTransfer->setUrl($categoryUrlPath);
            $this->urlFacade->updateUrl($urlTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param array<\Generated\Shared\Transfer\UrlTransfer> $urlTransfers
     * @param \Generated\Shared\Transfer\NodeTransfer $nodeTransfer
     *
     * @return void
     */
    protected function createCategoryNodeUrlsForNewLocalizedAttributes(
        CategoryTransfer $categoryTransfer,
        array $urlTransfers,
        NodeTransfer $nodeTransfer
    ): void {
        $categoryLocalizedAttributesTransfersWithoutUrl = $this->extractCategoryLocalizedAttributesTransfersWithoutUrl($categoryTransfer, $urlTransfers);

        if ($categoryLocalizedAttributesTransfersWithoutUrl->count()) {
            $this->categoryUrlCreator->createLocalizedCategoryUrlsForNode($nodeTransfer, $categoryLocalizedAttributesTransfersWithoutUrl);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param array<\Generated\Shared\Transfer\UrlTransfer> $urlTransfers
     *
     * @return \ArrayObject<array-key, \Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer>
     */
    protected function extractCategoryLocalizedAttributesTransfersWithoutUrl(CategoryTransfer $categoryTransfer, array $urlTransfers): ArrayObject
    {
        $categoryLocalizedAttributesTransfersWithoutUrl = new ArrayObject();
        foreach ($categoryTransfer->getLocalizedAttributes() as $categoryLocalizedAttributesTransfer) {
            if (!$this->urlForCategoryLocalizedAttributesTransferExists($categoryLocalizedAttributesTransfer, $urlTransfers)) {
                $categoryLocalizedAttributesTransfersWithoutUrl->append($categoryLocalizedAttributesTransfer);
            }
        }

        return $categoryLocalizedAttributesTransfersWithoutUrl;
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer $categoryLocalizedAttributesTransfer
     * @param array<\Generated\Shared\Transfer\UrlTransfer> $urlTransfers
     *
     * @return bool
     */
    protected function urlForCategoryLocalizedAttributesTransferExists(
        CategoryLocalizedAttributesTransfer $categoryLocalizedAttributesTransfer,
        array $urlTransfers
    ): bool {
        foreach ($urlTransfers as $urlTransfer) {
            if ($this->checkUrlLocale($urlTransfer, $categoryLocalizedAttributesTransfer->getLocaleOrFail())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer $urlTransfer
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return bool
     */
    protected function checkUrlLocale(UrlTransfer $urlTransfer, LocaleTransfer $localeTransfer): bool
    {
        return $urlTransfer->getFkLocaleOrFail() == $localeTransfer->getIdLocaleOrFail();
    }
}
