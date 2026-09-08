<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Reader;

use ArrayObject;
use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\CategoryConditionsTransfer;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\CategoryImageSetConditionsTransfer;
use Generated\Shared\Transfer\CategoryImageSetCriteriaTransfer;
use Generated\Shared\Transfer\CategoryNodeCriteriaTransfer;
use Generated\Shared\Transfer\CategoryNodeUrlCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTemplateCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Glue\Category\Api\Backend\Mapper\CategoryResourceMapperInterface;
use Spryker\Zed\Category\Business\CategoryFacadeInterface;
use Spryker\Zed\CategoryImage\Business\CategoryImageFacadeInterface;
use Spryker\Zed\Locale\Business\LocaleFacadeInterface;

class CategoryReader implements CategoryReaderInterface
{
    protected const string SORT_NODE_ALIAS_PREFIX = 'node.';

    /**
     * @uses \Orm\Zed\Category\Persistence\Map\SpyCategoryAttributeTableMap::TABLE_NAME
     */
    protected const string SORT_ATTRIBUTE_TABLE_PREFIX = 'spy_category_attribute.';

    public function __construct(
        protected CategoryFacadeInterface $categoryFacade,
        protected CategoryImageFacadeInterface $categoryImageFacade,
        protected LocaleFacadeInterface $localeFacade,
        protected CategoryResourceMapperInterface $categoryResourceMapper,
    ) {
    }

    public function findCategoryTransferByCategoryKey(string $categoryKey): ?CategoryTransfer
    {
        $categoryCollectionTransfer = $this->categoryFacade->getCategoryCollection(
            $this->createCategoryCriteriaTransfer($categoryKey),
        );

        if ($categoryCollectionTransfer->getCategories()->count() === 0) {
            return null;
        }

        /** @var \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer */
        $categoryTransfer = $categoryCollectionTransfer->getCategories()->getIterator()->current();

        return $this->categoryImageFacade->expandCategoryWithImageSets($categoryTransfer);
    }

    public function findCategoryResourceByCategoryKey(string $categoryKey): ?CategoriesBackendResource
    {
        $categoryTransfer = $this->findCategoryTransferByCategoryKey($categoryKey);

        if ($categoryTransfer === null) {
            return null;
        }

        $categoryCollectionTransfer = (new CategoryCollectionTransfer())->addCategory($categoryTransfer);
        $resources = $this->mapCategoryCollectionToResources($categoryCollectionTransfer);

        return $resources[0];
    }

    /**
     * {@inheritDoc}
     *
     * @param array<\Generated\Shared\Transfer\SortTransfer> $sortTransfers
     * @param array<int>|null $categoryIds
     *
     * @return array<\Generated\Api\Backend\CategoriesBackendResource>
     */
    public function getCategoryResourceCollection(
        PaginationTransfer $paginationTransfer,
        array $sortTransfers = [],
        ?array $categoryIds = null,
        ?LocaleTransfer $localeTransfer = null
    ): array {
        $categoryCriteriaTransfer = $this->createCategoryCriteriaTransfer()
            ->setPagination($paginationTransfer);

        if ($categoryIds !== null) {
            $categoryCriteriaTransfer->getCategoryConditionsOrFail()->setCategoryIds($categoryIds);
        }

        foreach ($sortTransfers as $sortTransfer) {
            $categoryCriteriaTransfer->addSort($sortTransfer);

            if (str_starts_with((string)$sortTransfer->getField(), static::SORT_NODE_ALIAS_PREFIX)) {
                $categoryCriteriaTransfer->getCategoryConditionsOrFail()->setIsMain(true);
            }

            if (str_starts_with((string)$sortTransfer->getField(), static::SORT_ATTRIBUTE_TABLE_PREFIX)) {
                $localeTransfer ??= $this->localeFacade->getCurrentLocale();
                $categoryCriteriaTransfer->getCategoryConditionsOrFail()->addLocaleName($localeTransfer->getLocaleNameOrFail());
            }
        }

        $categoryCollectionTransfer = $this->categoryFacade->getCategoryCollection($categoryCriteriaTransfer);
        $categoryCollectionTransfer = $this->expandCategoryCollectionWithImageSets($categoryCollectionTransfer);

        if ($categoryCollectionTransfer->getPagination() !== null) {
            $paginationTransfer->fromArray($categoryCollectionTransfer->getPaginationOrFail()->toArray(), true);
        }

        return $this->mapCategoryCollectionToResources($categoryCollectionTransfer);
    }

    protected function expandCategoryCollectionWithImageSets(CategoryCollectionTransfer $categoryCollectionTransfer): CategoryCollectionTransfer
    {
        $categoryIds = [];
        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            $categoryIds[] = $categoryTransfer->getIdCategoryOrFail();
        }

        if ($categoryIds === []) {
            return $categoryCollectionTransfer;
        }

        $categoryImageSetCollectionTransfer = $this->categoryImageFacade->getCategoryImageSetCollection(
            (new CategoryImageSetCriteriaTransfer())->setCategoryImageSetConditions(
                (new CategoryImageSetConditionsTransfer())->setCategoryIds($categoryIds),
            ),
        );

        $categoryImageSetTransfersIndexedByIdCategory = [];
        foreach ($categoryImageSetCollectionTransfer->getCategoryImageSets() as $categoryImageSetTransfer) {
            $categoryImageSetTransfersIndexedByIdCategory[$categoryImageSetTransfer->getIdCategoryOrFail()][] = $categoryImageSetTransfer;
        }

        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            $categoryTransfer->setImageSets(
                new ArrayObject($categoryImageSetTransfersIndexedByIdCategory[$categoryTransfer->getIdCategoryOrFail()] ?? []),
            );
        }

        return $categoryCollectionTransfer;
    }

    protected function createCategoryCriteriaTransfer(?string $categoryKey = null): CategoryCriteriaTransfer
    {
        $categoryConditionsTransfer = (new CategoryConditionsTransfer())->setWithParentCategory(true);

        if ($categoryKey !== null) {
            $categoryConditionsTransfer->addCategoryKey($categoryKey);
        }

        return (new CategoryCriteriaTransfer())->setCategoryConditions($categoryConditionsTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int>
     */
    public function getChildCategoryIdsByIdCategoryNode(int $idCategoryNode): array
    {
        $nodeCollectionTransfer = $this->categoryFacade->getCategoryNodes(
            (new CategoryNodeCriteriaTransfer())->addIdParentCategoryNode($idCategoryNode),
        );

        $childCategoryIds = [];
        foreach ($nodeCollectionTransfer->getNodes() as $nodeTransfer) {
            $childCategoryIds[] = $nodeTransfer->getFkCategoryOrFail();
        }

        return array_values(array_unique($childCategoryIds));
    }

    /**
     * @return array<\Generated\Api\Backend\CategoriesBackendResource>
     */
    protected function mapCategoryCollectionToResources(CategoryCollectionTransfer $categoryCollectionTransfer): array
    {
        $templateNamesIndexedByIdCategoryTemplate = $this->getTemplateNamesIndexedByIdCategoryTemplate();
        $categoryKeysIndexedByIdCategory = $this->getExtraParentCategoryKeysIndexedByIdCategory($categoryCollectionTransfer);
        $urlsIndexedByIdCategoryNodeAndLocaleName = $this->getUrlsIndexedByIdCategoryNodeAndLocaleName($categoryCollectionTransfer);

        $resources = [];
        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            $idMainCategoryNode = $categoryTransfer->getCategoryNode()?->getIdCategoryNode();

            $resources[] = $this->categoryResourceMapper->mapCategoryTransferToCategoriesBackendResource(
                $categoryTransfer,
                $templateNamesIndexedByIdCategoryTemplate,
                $categoryKeysIndexedByIdCategory,
                $urlsIndexedByIdCategoryNodeAndLocaleName[$idMainCategoryNode] ?? [],
            );
        }

        return $resources;
    }

    /**
     * @return array<int, string>
     */
    protected function getTemplateNamesIndexedByIdCategoryTemplate(): array
    {
        $templateNamesIndexedByIdCategoryTemplate = [];
        $categoryTemplateCollectionTransfer = $this->categoryFacade->getCategoryTemplateCollection(
            new CategoryTemplateCriteriaTransfer(),
        );

        foreach ($categoryTemplateCollectionTransfer->getCategoryTemplates() as $categoryTemplateTransfer) {
            $templateNamesIndexedByIdCategoryTemplate[$categoryTemplateTransfer->getIdCategoryTemplateOrFail()] = $categoryTemplateTransfer->getNameOrFail();
        }

        return $templateNamesIndexedByIdCategoryTemplate;
    }

    /**
     * @return array<int, string>
     */
    protected function getExtraParentCategoryKeysIndexedByIdCategory(CategoryCollectionTransfer $categoryCollectionTransfer): array
    {
        $extraParentCategoryIds = [];
        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            foreach ($categoryTransfer->getExtraParents() as $extraParentNodeTransfer) {
                if ($extraParentNodeTransfer->getFkCategory() !== null) {
                    $extraParentCategoryIds[] = $extraParentNodeTransfer->getFkCategoryOrFail();
                }
            }
        }

        if ($extraParentCategoryIds === []) {
            return [];
        }

        $extraParentCategoryCollectionTransfer = $this->categoryFacade->getCategoryCollection(
            (new CategoryCriteriaTransfer())->setCategoryConditions(
                (new CategoryConditionsTransfer())->setCategoryIds(array_values(array_unique($extraParentCategoryIds))),
            ),
        );

        $categoryKeysIndexedByIdCategory = [];
        foreach ($extraParentCategoryCollectionTransfer->getCategories() as $categoryTransfer) {
            $categoryKeysIndexedByIdCategory[$categoryTransfer->getIdCategoryOrFail()] = $categoryTransfer->getCategoryKeyOrFail();
        }

        return $categoryKeysIndexedByIdCategory;
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function getUrlsIndexedByIdCategoryNodeAndLocaleName(CategoryCollectionTransfer $categoryCollectionTransfer): array
    {
        $mainCategoryNodeIds = [];
        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            if ($categoryTransfer->getCategoryNode()?->getIdCategoryNode() !== null) {
                $mainCategoryNodeIds[] = $categoryTransfer->getCategoryNodeOrFail()->getIdCategoryNodeOrFail();
            }
        }

        if ($mainCategoryNodeIds === []) {
            return [];
        }

        $localeNamesIndexedByIdLocale = [];
        foreach ($this->localeFacade->getLocaleCollection() as $localeTransfer) {
            $localeNamesIndexedByIdLocale[$localeTransfer->getIdLocaleOrFail()] = $localeTransfer->getLocaleNameOrFail();
        }

        $urlTransfers = $this->categoryFacade->getCategoryNodeUrls(
            (new CategoryNodeUrlCriteriaTransfer())->setCategoryNodeIds($mainCategoryNodeIds),
        );

        $urlsIndexedByIdCategoryNodeAndLocaleName = [];
        foreach ($urlTransfers as $urlTransfer) {
            $localeName = $localeNamesIndexedByIdLocale[$urlTransfer->getFkLocale()] ?? null;
            if ($localeName === null || $urlTransfer->getFkResourceCategorynode() === null) {
                continue;
            }

            $urlsIndexedByIdCategoryNodeAndLocaleName[$urlTransfer->getFkResourceCategorynodeOrFail()][$localeName] = $urlTransfer->getUrlOrFail();
        }

        return $urlsIndexedByIdCategoryNodeAndLocaleName;
    }
}
