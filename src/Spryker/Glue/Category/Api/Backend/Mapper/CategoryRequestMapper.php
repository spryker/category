<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Mapper;

use ArrayObject;
use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryImageSetTransfer;
use Generated\Shared\Transfer\CategoryImageTransfer;
use Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\CategoryWriteContextTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;

class CategoryRequestMapper implements CategoryRequestMapperInterface
{
    protected const string KEY_LOCALE_NAME = 'localeName';

    protected const string KEY_NAME = 'name';

    protected const string KEY_META_TITLE = 'metaTitle';

    protected const string KEY_META_DESCRIPTION = 'metaDescription';

    protected const string KEY_META_KEYWORDS = 'metaKeywords';

    protected const string KEY_IMAGES = 'images';

    protected const string KEY_EXTERNAL_URL_SMALL = 'externalUrlSmall';

    protected const string KEY_EXTERNAL_URL_LARGE = 'externalUrlLarge';

    protected const string KEY_SORT_ORDER = 'sortOrder';

    protected const bool DEFAULT_IS_ACTIVE = false;

    protected const bool DEFAULT_IS_IN_MENU = true;

    protected const bool DEFAULT_IS_SEARCHABLE = true;

    protected const bool DEFAULT_IS_CLICKABLE = true;

    public function mapResourceToNewCategoryTransfer(
        CategoriesBackendResource $categoriesBackendResource,
        CategoryWriteContextTransfer $categoryWriteContextTransfer
    ): CategoryTransfer {
        $categoryTransfer = (new CategoryTransfer())
            ->setCategoryKey($categoriesBackendResource->categoryKey)
            ->setIsActive($categoriesBackendResource->isActive ?? static::DEFAULT_IS_ACTIVE)
            ->setIsInMenu($categoriesBackendResource->isInMenu ?? static::DEFAULT_IS_IN_MENU)
            ->setIsSearchable($categoriesBackendResource->isSearchable ?? static::DEFAULT_IS_SEARCHABLE)
            ->setIsClickable($categoriesBackendResource->isClickable ?? static::DEFAULT_IS_CLICKABLE);

        $categoryTemplateTransfer = $categoryWriteContextTransfer->getCategoryTemplate();
        if ($categoryTemplateTransfer !== null) {
            $categoryTransfer
                ->setCategoryTemplate($categoryTemplateTransfer)
                ->setFkCategoryTemplate($categoryTemplateTransfer->getIdCategoryTemplateOrFail());
        }

        $isRoot = $categoriesBackendResource->isRoot === true;
        $categoryNodeTransfer = (new NodeTransfer())->setIsRoot($isRoot);
        if ($categoriesBackendResource->position !== null) {
            $categoryNodeTransfer->setNodeOrder($categoriesBackendResource->position);
        }

        $categoryTransfer->setCategoryNode($categoryNodeTransfer);

        $mainNodesIndexedByCategoryKey = $this->getMainNodesIndexedByCategoryKey($categoryWriteContextTransfer);

        if (!$isRoot && $categoriesBackendResource->parentCategoryKey !== null) {
            $categoryTransfer->setParentCategoryNode($mainNodesIndexedByCategoryKey[$this->normalizeCategoryKey($categoriesBackendResource->parentCategoryKey)]);
        }

        foreach ($categoriesBackendResource->extraParentCategoryKeys ?? [] as $extraParentCategoryKey) {
            $categoryTransfer->addExtraParent($mainNodesIndexedByCategoryKey[$this->normalizeCategoryKey($extraParentCategoryKey)]);
        }

        $localeTransfersIndexedByLocaleName = $this->getLocaleTransfersIndexedByLocaleName($categoryWriteContextTransfer);

        $categoryTransfer->setLocalizedAttributes(
            $this->mergeLocalizedAttributes($categoriesBackendResource->localizedAttributes, new ArrayObject(), $localeTransfersIndexedByLocaleName),
        );

        $categoryTransfer->setStoreRelation($this->mapStoresToStoreRelationTransfer($categoryWriteContextTransfer));
        $categoryTransfer->setImageSets($this->mapImageSets($categoriesBackendResource->imageSets ?? [], $localeTransfersIndexedByLocaleName));

        return $categoryTransfer;
    }

    public function mapResourceToExistingCategoryTransfer(
        CategoriesBackendResource $categoriesBackendResource,
        CategoryTransfer $categoryTransfer,
        CategoryWriteContextTransfer $categoryWriteContextTransfer
    ): CategoryTransfer {
        if ($categoriesBackendResource->isActive !== null) {
            $categoryTransfer->setIsActive($categoriesBackendResource->isActive);
        }

        if ($categoriesBackendResource->isInMenu !== null) {
            $categoryTransfer->setIsInMenu($categoriesBackendResource->isInMenu);
        }

        if ($categoriesBackendResource->isSearchable !== null) {
            $categoryTransfer->setIsSearchable($categoriesBackendResource->isSearchable);
        }

        if ($categoriesBackendResource->isClickable !== null) {
            $categoryTransfer->setIsClickable($categoriesBackendResource->isClickable);
        }

        $categoryTemplateTransfer = $categoryWriteContextTransfer->getCategoryTemplate();
        if ($categoriesBackendResource->templateName !== null && $categoryTemplateTransfer !== null) {
            $categoryTransfer
                ->setCategoryTemplate($categoryTemplateTransfer)
                ->setFkCategoryTemplate($categoryTemplateTransfer->getIdCategoryTemplateOrFail());
        }

        if ($categoriesBackendResource->position !== null) {
            $categoryTransfer->getCategoryNodeOrFail()->setNodeOrder($categoriesBackendResource->position);
        }

        $mainNodesIndexedByCategoryKey = $this->getMainNodesIndexedByCategoryKey($categoryWriteContextTransfer);

        if ($categoriesBackendResource->parentCategoryKey !== null) {
            $categoryTransfer->setParentCategoryNode($mainNodesIndexedByCategoryKey[$this->normalizeCategoryKey($categoriesBackendResource->parentCategoryKey)]);
        }

        $categoryTransfer->setExtraParents(
            $this->mapExtraParentsForUpdate($categoriesBackendResource, $categoryTransfer, $mainNodesIndexedByCategoryKey),
        );

        $localeTransfersIndexedByLocaleName = $this->getLocaleTransfersIndexedByLocaleName($categoryWriteContextTransfer);

        if ($categoriesBackendResource->localizedAttributes !== []) {
            $categoryTransfer->setLocalizedAttributes(
                $this->mergeLocalizedAttributes($categoriesBackendResource->localizedAttributes, $categoryTransfer->getLocalizedAttributes(), $localeTransfersIndexedByLocaleName),
            );
        }

        if ($categoriesBackendResource->stores !== null) {
            $categoryTransfer->setStoreRelation($this->mapStoresToStoreRelationTransfer($categoryWriteContextTransfer));
        }

        if ($categoriesBackendResource->imageSets !== null) {
            $categoryTransfer->setImageSets($this->mapImageSets($categoriesBackendResource->imageSets, $localeTransfersIndexedByLocaleName));
        }

        return $categoryTransfer;
    }

    protected function normalizeCategoryKey(string $categoryKey): string
    {
        return mb_strtolower($categoryKey);
    }

    /**
     * @return array<string, \Generated\Shared\Transfer\NodeTransfer>
     */
    protected function getMainNodesIndexedByCategoryKey(CategoryWriteContextTransfer $categoryWriteContextTransfer): array
    {
        $mainNodesIndexedByCategoryKey = [];
        foreach ($categoryWriteContextTransfer->getCategories() as $categoryTransfer) {
            if ($categoryTransfer->getCategoryNode() === null) {
                continue;
            }

            $mainNodesIndexedByCategoryKey[$this->normalizeCategoryKey($categoryTransfer->getCategoryKeyOrFail())] = $categoryTransfer->getCategoryNodeOrFail();
        }

        return $mainNodesIndexedByCategoryKey;
    }

    /**
     * @return array<string, \Generated\Shared\Transfer\LocaleTransfer>
     */
    protected function getLocaleTransfersIndexedByLocaleName(CategoryWriteContextTransfer $categoryWriteContextTransfer): array
    {
        $localeTransfersIndexedByLocaleName = [];
        foreach ($categoryWriteContextTransfer->getLocales() as $localeTransfer) {
            $localeTransfersIndexedByLocaleName[$localeTransfer->getLocaleNameOrFail()] = $localeTransfer;
        }

        return $localeTransfersIndexedByLocaleName;
    }

    /**
     * @param array<string, \Generated\Shared\Transfer\NodeTransfer> $mainNodesIndexedByCategoryKey
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\NodeTransfer>
     */
    protected function mapExtraParentsForUpdate(
        CategoriesBackendResource $categoriesBackendResource,
        CategoryTransfer $categoryTransfer,
        array $mainNodesIndexedByCategoryKey
    ): ArrayObject {
        $placementNodesIndexedByIdParentNode = [];
        foreach ($categoryTransfer->getNodeCollection()?->getNodes() ?? [] as $nodeTransfer) {
            if ($nodeTransfer->getIsMain() === false && $nodeTransfer->getFkParentCategoryNode() !== null) {
                $placementNodesIndexedByIdParentNode[$nodeTransfer->getFkParentCategoryNodeOrFail()] = $nodeTransfer;
            }
        }

        if ($categoriesBackendResource->extraParentCategoryKeys === null) {
            return new ArrayObject(array_values($placementNodesIndexedByIdParentNode));
        }

        $extraParentNodeTransfers = new ArrayObject();
        foreach ($categoriesBackendResource->extraParentCategoryKeys as $extraParentCategoryKey) {
            $parentMainNodeTransfer = $mainNodesIndexedByCategoryKey[$this->normalizeCategoryKey($extraParentCategoryKey)];
            $extraParentNodeTransfers->append(
                $placementNodesIndexedByIdParentNode[$parentMainNodeTransfer->getIdCategoryNode()] ?? $parentMainNodeTransfer,
            );
        }

        return $extraParentNodeTransfers;
    }

    /**
     * @param array<array<string, mixed>|object> $localizedAttributeEntries
     * @param \ArrayObject<int, \Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer> $categoryLocalizedAttributesTransfers
     * @param array<string, \Generated\Shared\Transfer\LocaleTransfer> $localeTransfersIndexedByLocaleName
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer>
     */
    protected function mergeLocalizedAttributes(
        array $localizedAttributeEntries,
        ArrayObject $categoryLocalizedAttributesTransfers,
        array $localeTransfersIndexedByLocaleName
    ): ArrayObject {
        $transfersIndexedByLocaleName = [];
        foreach ($categoryLocalizedAttributesTransfers as $categoryLocalizedAttributesTransfer) {
            $localeName = $categoryLocalizedAttributesTransfer->getLocaleOrFail()->getLocaleNameOrFail();
            $transfersIndexedByLocaleName[$localeName] = $categoryLocalizedAttributesTransfer;
        }

        foreach ($localizedAttributeEntries as $localizedAttributeEntry) {
            $localizedAttributeEntry = $this->normalizeEntry($localizedAttributeEntry);
            $localeName = $localizedAttributeEntry[static::KEY_LOCALE_NAME] ?? null;

            if ($localeName === null || !isset($localeTransfersIndexedByLocaleName[$localeName])) {
                continue;
            }

            if (!isset($transfersIndexedByLocaleName[$localeName])) {
                $categoryLocalizedAttributesTransfer = (new CategoryLocalizedAttributesTransfer())
                    ->setLocale($localeTransfersIndexedByLocaleName[$localeName]);
                $categoryLocalizedAttributesTransfers->append($categoryLocalizedAttributesTransfer);
                $transfersIndexedByLocaleName[$localeName] = $categoryLocalizedAttributesTransfer;
            }

            $categoryLocalizedAttributesTransfer = $transfersIndexedByLocaleName[$localeName];

            $providedFields = [];
            foreach ([static::KEY_NAME, static::KEY_META_TITLE, static::KEY_META_DESCRIPTION, static::KEY_META_KEYWORDS] as $field) {
                if (($localizedAttributeEntry[$field] ?? null) !== null) {
                    $providedFields[$field] = $localizedAttributeEntry[$field];
                }
            }

            if ($providedFields !== []) {
                $categoryLocalizedAttributesTransfer->fromArray($providedFields, true);
            }
        }

        return $categoryLocalizedAttributesTransfers;
    }

    protected function mapStoresToStoreRelationTransfer(CategoryWriteContextTransfer $categoryWriteContextTransfer): StoreRelationTransfer
    {
        $storeRelationTransfer = new StoreRelationTransfer();

        foreach ($categoryWriteContextTransfer->getStores() as $storeTransfer) {
            $storeRelationTransfer->addStores($storeTransfer);
            $storeRelationTransfer->addIdStores($storeTransfer->getIdStoreOrFail());
        }

        return $storeRelationTransfer;
    }

    /**
     * @param array<array<string, mixed>|object> $imageSetEntries
     * @param array<string, \Generated\Shared\Transfer\LocaleTransfer> $localeTransfersIndexedByLocaleName
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\CategoryImageSetTransfer>
     */
    protected function mapImageSets(array $imageSetEntries, array $localeTransfersIndexedByLocaleName): ArrayObject
    {
        $categoryImageSetTransfers = new ArrayObject();

        foreach ($imageSetEntries as $imageSetEntry) {
            $imageSetEntry = $this->normalizeEntry($imageSetEntry);
            $localeName = $imageSetEntry[static::KEY_LOCALE_NAME] ?? null;

            if ($localeName === null || !isset($localeTransfersIndexedByLocaleName[$localeName])) {
                continue;
            }

            $categoryImageSetTransfer = (new CategoryImageSetTransfer())
                ->setName($imageSetEntry[static::KEY_NAME] ?? null)
                ->setLocale($localeTransfersIndexedByLocaleName[$localeName]);

            foreach ($imageSetEntry[static::KEY_IMAGES] ?? [] as $imageEntry) {
                $imageEntry = $this->normalizeEntry($imageEntry);

                $categoryImageSetTransfer->addCategoryImage(
                    (new CategoryImageTransfer())
                        ->setExternalUrlSmall($imageEntry[static::KEY_EXTERNAL_URL_SMALL] ?? null)
                        ->setExternalUrlLarge($imageEntry[static::KEY_EXTERNAL_URL_LARGE] ?? null)
                        ->setSortOrder($imageEntry[static::KEY_SORT_ORDER] ?? null),
                );
            }

            $categoryImageSetTransfers->append($categoryImageSetTransfer);
        }

        return $categoryImageSetTransfers;
    }

    /**
     * @param object|array<string, mixed> $entry
     *
     * @return array<string, mixed>
     */
    protected function normalizeEntry(array|object $entry): array
    {
        if (is_array($entry)) {
            return $entry;
        }

        return get_object_vars($entry);
    }
}
