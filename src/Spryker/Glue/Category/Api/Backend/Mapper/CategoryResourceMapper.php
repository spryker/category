<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Mapper;

use Generated\Api\Backend\Categories\CategoriesImageSetsBackendObject;
use Generated\Api\Backend\Categories\CategoriesImageSetsImagesBackendObject;
use Generated\Api\Backend\Categories\CategoriesLocalizedAttributesBackendObject;
use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryTransfer;

class CategoryResourceMapper implements CategoryResourceMapperInterface
{
    /**
     * {@inheritDoc}
     *
     * @param array<int, string> $templateNamesIndexedByIdCategoryTemplate
     * @param array<int, string> $categoryKeysIndexedByIdCategory
     * @param array<string, string> $urlsIndexedByLocaleName
     */
    public function mapCategoryTransferToCategoriesBackendResource(
        CategoryTransfer $categoryTransfer,
        array $templateNamesIndexedByIdCategoryTemplate,
        array $categoryKeysIndexedByIdCategory,
        array $urlsIndexedByLocaleName
    ): CategoriesBackendResource {
        $categoriesBackendResource = new CategoriesBackendResource();
        $categoriesBackendResource->categoryKey = $categoryTransfer->getCategoryKey();
        $categoriesBackendResource->uuid = $categoryTransfer->getUuid();
        $categoriesBackendResource->isActive = $categoryTransfer->getIsActive();
        $categoriesBackendResource->isInMenu = $categoryTransfer->getIsInMenu();
        $categoriesBackendResource->isSearchable = $categoryTransfer->getIsSearchable();
        $categoriesBackendResource->isClickable = $categoryTransfer->getIsClickable();
        $categoriesBackendResource->templateName = $templateNamesIndexedByIdCategoryTemplate[$categoryTransfer->getFkCategoryTemplate()] ?? null;
        $categoriesBackendResource->parentCategoryKey = $this->mapParentCategoryKey($categoryTransfer);
        $categoriesBackendResource->position = $categoryTransfer->getCategoryNode()?->getNodeOrder();
        $categoriesBackendResource->isRoot = (bool)$categoryTransfer->getCategoryNode()?->getIsRoot();
        $categoriesBackendResource->extraParentCategoryKeys = $this->mapExtraParentCategoryKeys($categoryTransfer, $categoryKeysIndexedByIdCategory);
        $categoriesBackendResource->localizedAttributes = $this->mapLocalizedAttributes($categoryTransfer, $urlsIndexedByLocaleName);
        $categoriesBackendResource->stores = $this->mapStores($categoryTransfer);
        $categoriesBackendResource->imageSets = $this->mapImageSets($categoryTransfer);

        return $categoriesBackendResource;
    }

    protected function mapParentCategoryKey(CategoryTransfer $categoryTransfer): ?string
    {
        return $categoryTransfer->getParentCategoryNode()?->getCategory()?->getCategoryKey();
    }

    /**
     * @param array<int, string> $categoryKeysIndexedByIdCategory
     *
     * @return array<string>
     */
    protected function mapExtraParentCategoryKeys(CategoryTransfer $categoryTransfer, array $categoryKeysIndexedByIdCategory): array
    {
        $extraParentCategoryKeys = [];
        foreach ($categoryTransfer->getExtraParents() as $extraParentNodeTransfer) {
            $categoryKey = $categoryKeysIndexedByIdCategory[$extraParentNodeTransfer->getFkCategory()] ?? null;
            if ($categoryKey !== null) {
                $extraParentCategoryKeys[] = $categoryKey;
            }
        }

        return $extraParentCategoryKeys;
    }

    /**
     * @param array<string, string> $urlsIndexedByLocaleName
     *
     * @return array<\Generated\Api\Backend\Categories\CategoriesLocalizedAttributesBackendObject>
     */
    protected function mapLocalizedAttributes(CategoryTransfer $categoryTransfer, array $urlsIndexedByLocaleName): array
    {
        $localizedAttributes = [];
        foreach ($categoryTransfer->getLocalizedAttributes() as $categoryLocalizedAttributesTransfer) {
            $localeName = $categoryLocalizedAttributesTransfer->getLocale()?->getLocaleName();

            $localizedAttributes[] = CategoriesLocalizedAttributesBackendObject::fromArray([
                'localeName' => $localeName,
                'name' => $categoryLocalizedAttributesTransfer->getName(),
                'metaTitle' => $categoryLocalizedAttributesTransfer->getMetaTitle(),
                'metaDescription' => $categoryLocalizedAttributesTransfer->getMetaDescription(),
                'metaKeywords' => $categoryLocalizedAttributesTransfer->getMetaKeywords(),
                'url' => $localeName !== null ? ($urlsIndexedByLocaleName[$localeName] ?? null) : null,
            ]);
        }

        return $localizedAttributes;
    }

    /**
     * @return array<string>
     */
    protected function mapStores(CategoryTransfer $categoryTransfer): array
    {
        $storeNames = [];
        foreach ($categoryTransfer->getStoreRelation()?->getStores() ?? [] as $storeTransfer) {
            $storeNames[] = $storeTransfer->getNameOrFail();
        }

        return $storeNames;
    }

    /**
     * @return array<\Generated\Api\Backend\Categories\CategoriesImageSetsBackendObject>
     */
    protected function mapImageSets(CategoryTransfer $categoryTransfer): array
    {
        $imageSets = [];
        foreach ($categoryTransfer->getImageSets() as $categoryImageSetTransfer) {
            $images = [];
            foreach ($categoryImageSetTransfer->getCategoryImages() as $categoryImageTransfer) {
                $images[] = CategoriesImageSetsImagesBackendObject::fromArray([
                    'externalUrlSmall' => $categoryImageTransfer->getExternalUrlSmall(),
                    'externalUrlLarge' => $categoryImageTransfer->getExternalUrlLarge(),
                    'sortOrder' => $categoryImageTransfer->getSortOrder(),
                ]);
            }

            $imageSets[] = CategoriesImageSetsBackendObject::fromArray([
                'localeName' => $categoryImageSetTransfer->getLocale()?->getLocaleName(),
                'name' => $categoryImageSetTransfer->getName(),
                'images' => $images,
            ]);
        }

        return $imageSets;
    }
}
