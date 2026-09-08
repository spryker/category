<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Validator;

use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryConditionsTransfer;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\CategoryNodeCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\CategoryWriteContextTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Spryker\Zed\Category\Business\CategoryFacadeInterface;

class CategoryWriteValidator implements CategoryWriteValidatorInterface
{
    protected const string KEY_LOCALE_NAME = 'localeName';

    protected const string KEY_NAME = 'name';

    protected const string ERROR_CATEGORY_KEY_EXISTS = 'A category with categoryKey "%s" already exists.';

    protected const string ERROR_PARENT_REQUIRED = 'parentCategoryKey is required unless isRoot is true.';

    protected const string ERROR_PARENT_FORBIDDEN_FOR_ROOT = 'parentCategoryKey must be omitted when isRoot is true.';

    protected const string ERROR_ROOT_ALREADY_EXISTS = 'A root category already exists; only one root category is allowed.';

    protected const string ERROR_TEMPLATE_NOT_FOUND = 'Category template "%s" does not exist. Available templates: %s.';

    protected const string ERROR_PARENT_NOT_FOUND = 'Parent category "%s" does not exist.';

    protected const string ERROR_LOCALE_UNKNOWN = 'Locale "%s" is not available.';

    protected const string ERROR_LOCALE_NAME_MISSING = 'localizedAttributes entries must provide a localeName.';

    protected const string ERROR_LOCALIZED_ATTRIBUTES_REQUIRED = 'localizedAttributes is required and must not be empty.';

    protected const string ERROR_LOCALIZED_NAME_MISSING = 'localizedAttributes entries must provide a non-empty name for locale "%s".';

    protected const string ERROR_LOCALIZED_NAME_MISSING_FOR_LOCALES = 'Missing localized name for locale(s): %s.';

    protected const string ERROR_STORE_NOT_FOUND = 'Store "%s" does not exist.';

    protected const string ERROR_SIBLING_NAME_EXISTS = 'A category named "%s" already exists on the same level.';

    protected const string ERROR_CATEGORY_KEY_IMMUTABLE = 'categoryKey is immutable and cannot be changed.';

    protected const string ERROR_ROOT_STATUS_IMMUTABLE = 'Root status of a category cannot be changed.';

    protected const string ERROR_ROOT_CANNOT_BE_MOVED = 'A root category cannot be moved under a parent category.';

    protected const string ERROR_PARENT_CYCLE = 'Parent category "%s" must not be the category itself or one of its descendants.';

    protected const string ERROR_ROOT_CANNOT_BE_DELETED = 'A root category cannot be deleted.';

    public function __construct(protected CategoryFacadeInterface $categoryFacade)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string>
     */
    public function validateCreate(
        CategoriesBackendResource $categoriesBackendResource,
        CategoryWriteContextTransfer $categoryWriteContextTransfer
    ): array {
        $errors = [];
        $isRoot = $categoriesBackendResource->isRoot === true;
        $mainNodesIndexedByCategoryKey = $this->getMainNodesIndexedByCategoryKey($categoryWriteContextTransfer);

        if (
            $categoriesBackendResource->categoryKey !== null
            && $this->categoryKeyExists($categoriesBackendResource->categoryKey, $categoryWriteContextTransfer)
        ) {
            $errors[] = sprintf(static::ERROR_CATEGORY_KEY_EXISTS, $categoriesBackendResource->categoryKey);
        }

        if ($isRoot && $categoriesBackendResource->parentCategoryKey !== null) {
            $errors[] = static::ERROR_PARENT_FORBIDDEN_FOR_ROOT;
        }

        if (!$isRoot && $categoriesBackendResource->parentCategoryKey === null) {
            $errors[] = static::ERROR_PARENT_REQUIRED;
        }

        if ($isRoot && $this->rootCategoryExists()) {
            $errors[] = static::ERROR_ROOT_ALREADY_EXISTS;
        }

        $errors = array_merge($errors, $this->validateTemplate($categoriesBackendResource->templateName, $categoryWriteContextTransfer));

        $parentCategoryKeys = $this->collectParentCategoryKeys($categoriesBackendResource);
        $errors = array_merge($errors, $this->validateParentsExist($parentCategoryKeys, $mainNodesIndexedByCategoryKey));

        $errors = array_merge($errors, $this->validateLocalizedAttributes($categoriesBackendResource->localizedAttributes, true, $categoryWriteContextTransfer));
        $errors = array_merge($errors, $this->validateStores($categoriesBackendResource->stores ?? [], $categoryWriteContextTransfer));

        if ($errors !== []) {
            return $errors;
        }

        $parentNodeTransfer = $categoriesBackendResource->parentCategoryKey !== null
            ? ($mainNodesIndexedByCategoryKey[$this->normalizeCategoryKey($categoriesBackendResource->parentCategoryKey)] ?? null)
            : null;

        return $this->validateSiblingNames(
            $categoriesBackendResource->localizedAttributes,
            $parentNodeTransfer,
            null,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string>
     */
    public function validateUpdate(
        CategoriesBackendResource $categoriesBackendResource,
        CategoryTransfer $currentCategoryTransfer,
        CategoryWriteContextTransfer $categoryWriteContextTransfer
    ): array {
        $errors = [];
        $isCurrentlyRoot = (bool)$currentCategoryTransfer->getCategoryNode()?->getIsRoot();
        $mainNodesIndexedByCategoryKey = $this->getMainNodesIndexedByCategoryKey($categoryWriteContextTransfer);

        if (
            $categoriesBackendResource->categoryKey !== null
            && $categoriesBackendResource->categoryKey !== $currentCategoryTransfer->getCategoryKey()
        ) {
            $errors[] = static::ERROR_CATEGORY_KEY_IMMUTABLE;
        }

        if ($categoriesBackendResource->isRoot !== null && $categoriesBackendResource->isRoot !== $isCurrentlyRoot) {
            $errors[] = static::ERROR_ROOT_STATUS_IMMUTABLE;
        }

        if ($categoriesBackendResource->parentCategoryKey !== null && $isCurrentlyRoot) {
            $errors[] = static::ERROR_ROOT_CANNOT_BE_MOVED;
        }

        $errors = array_merge($errors, $this->validateTemplate($categoriesBackendResource->templateName, $categoryWriteContextTransfer));

        $parentCategoryKeys = $this->collectParentCategoryKeys($categoriesBackendResource);
        $errors = array_merge($errors, $this->validateParentsExist($parentCategoryKeys, $mainNodesIndexedByCategoryKey));
        $errors = array_merge($errors, $this->validateParentCycles($parentCategoryKeys, $mainNodesIndexedByCategoryKey, $currentCategoryTransfer));

        $errors = array_merge($errors, $this->validateLocalizedAttributes($categoriesBackendResource->localizedAttributes, false, $categoryWriteContextTransfer));
        $errors = array_merge($errors, $this->validateStores($categoriesBackendResource->stores ?? [], $categoryWriteContextTransfer));

        if ($errors !== []) {
            return $errors;
        }

        $targetParentNodeTransfer = $categoriesBackendResource->parentCategoryKey !== null
            ? ($mainNodesIndexedByCategoryKey[$this->normalizeCategoryKey($categoriesBackendResource->parentCategoryKey)] ?? null)
            : $currentCategoryTransfer->getParentCategoryNode();

        return $this->validateSiblingNames(
            $categoriesBackendResource->localizedAttributes,
            $targetParentNodeTransfer,
            $currentCategoryTransfer->getIdCategory(),
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return array<string>
     */
    public function validateDelete(CategoryTransfer $currentCategoryTransfer): array
    {
        if ($currentCategoryTransfer->getCategoryNode()?->getIsRoot()) {
            return [static::ERROR_ROOT_CANNOT_BE_DELETED];
        }

        return [];
    }

    protected function categoryKeyExists(string $categoryKey, CategoryWriteContextTransfer $categoryWriteContextTransfer): bool
    {
        $normalizedCategoryKey = $this->normalizeCategoryKey($categoryKey);
        foreach ($categoryWriteContextTransfer->getCategories() as $categoryTransfer) {
            if ($this->normalizeCategoryKey($categoryTransfer->getCategoryKeyOrFail()) === $normalizedCategoryKey) {
                return true;
            }
        }

        return false;
    }

    protected function rootCategoryExists(): bool
    {
        $categoryCollectionTransfer = $this->categoryFacade->getCategoryCollection(
            (new CategoryCriteriaTransfer())->setCategoryConditions(
                (new CategoryConditionsTransfer())->setIsRoot(true),
            ),
        );

        return $categoryCollectionTransfer->getCategories()->count() > 0;
    }

    /**
     * @return array<string>
     */
    protected function validateTemplate(?string $templateName, CategoryWriteContextTransfer $categoryWriteContextTransfer): array
    {
        if ($templateName === null || $categoryWriteContextTransfer->getCategoryTemplate() !== null) {
            return [];
        }

        $templateNames = [];
        foreach ($categoryWriteContextTransfer->getCategoryTemplates() as $categoryTemplateTransfer) {
            $templateNames[] = sprintf('"%s"', $categoryTemplateTransfer->getNameOrFail());
        }

        return [sprintf(static::ERROR_TEMPLATE_NOT_FOUND, trim($templateName), implode(', ', $templateNames))];
    }

    protected function normalizeCategoryKey(string $categoryKey): string
    {
        return mb_strtolower($categoryKey);
    }

    /**
     * @return array<string>
     */
    protected function collectParentCategoryKeys(CategoriesBackendResource $categoriesBackendResource): array
    {
        return array_merge(
            $categoriesBackendResource->parentCategoryKey !== null ? [$categoriesBackendResource->parentCategoryKey] : [],
            $categoriesBackendResource->extraParentCategoryKeys ?? [],
        );
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
     * @param array<string> $parentCategoryKeys
     * @param array<string, \Generated\Shared\Transfer\NodeTransfer> $mainNodesIndexedByCategoryKey
     *
     * @return array<string>
     */
    protected function validateParentsExist(array $parentCategoryKeys, array $mainNodesIndexedByCategoryKey): array
    {
        $errors = [];
        foreach (array_unique($parentCategoryKeys) as $parentCategoryKey) {
            if (!isset($mainNodesIndexedByCategoryKey[$this->normalizeCategoryKey($parentCategoryKey)])) {
                $errors[] = sprintf(static::ERROR_PARENT_NOT_FOUND, $parentCategoryKey);
            }
        }

        return $errors;
    }

    /**
     * @param array<string> $parentCategoryKeys
     * @param array<string, \Generated\Shared\Transfer\NodeTransfer> $mainNodesIndexedByCategoryKey
     *
     * @return array<string>
     */
    protected function validateParentCycles(
        array $parentCategoryKeys,
        array $mainNodesIndexedByCategoryKey,
        CategoryTransfer $currentCategoryTransfer
    ): array {
        if ($parentCategoryKeys === []) {
            return [];
        }

        $ownCategoryKey = $this->normalizeCategoryKey($currentCategoryTransfer->getCategoryKeyOrFail());
        $errors = [];
        $parentNodeIds = [];

        foreach (array_unique($parentCategoryKeys) as $parentCategoryKey) {
            if ($this->normalizeCategoryKey($parentCategoryKey) === $ownCategoryKey) {
                $errors[] = sprintf(static::ERROR_PARENT_CYCLE, $parentCategoryKey);

                continue;
            }

            $parentNodeTransfer = $mainNodesIndexedByCategoryKey[$this->normalizeCategoryKey($parentCategoryKey)] ?? null;
            if ($parentNodeTransfer?->getIdCategoryNode() !== null) {
                $parentNodeIds[$parentCategoryKey] = $parentNodeTransfer->getIdCategoryNodeOrFail();
            }
        }

        if ($parentNodeIds === []) {
            return $errors;
        }

        $ascendantCategoryKeysIndexedByIdCategoryNode = $this->categoryFacade->getAscendantCategoryKeysGroupedByIdCategoryNode(
            (new CategoryNodeCriteriaTransfer())->setCategoryNodeIds(array_values($parentNodeIds)),
        );

        foreach ($parentNodeIds as $parentCategoryKey => $idParentCategoryNode) {
            $ascendantCategoryKeys = array_map($this->normalizeCategoryKey(...), $ascendantCategoryKeysIndexedByIdCategoryNode[$idParentCategoryNode] ?? []);
            if (in_array($ownCategoryKey, $ascendantCategoryKeys, true)) {
                $errors[] = sprintf(static::ERROR_PARENT_CYCLE, $parentCategoryKey);
            }
        }

        return $errors;
    }

    /**
     * @param array<array<string, mixed>|object> $localizedAttributeEntries
     *
     * @return array<string>
     */
    protected function validateLocalizedAttributes(
        array $localizedAttributeEntries,
        bool $isNameRequired,
        CategoryWriteContextTransfer $categoryWriteContextTransfer
    ): array {
        if ($isNameRequired && $localizedAttributeEntries === []) {
            return [static::ERROR_LOCALIZED_ATTRIBUTES_REQUIRED];
        }

        $availableLocaleNames = [];
        foreach ($categoryWriteContextTransfer->getLocales() as $localeTransfer) {
            $availableLocaleNames[] = $localeTransfer->getLocaleNameOrFail();
        }

        $errors = [];
        $providedLocaleNames = [];
        foreach ($localizedAttributeEntries as $localizedAttributeEntry) {
            $localizedAttributeEntry = $this->normalizeEntry($localizedAttributeEntry);
            $localeName = $localizedAttributeEntry[static::KEY_LOCALE_NAME] ?? null;

            if ($localeName === null || $localeName === '') {
                $errors[] = static::ERROR_LOCALE_NAME_MISSING;

                continue;
            }

            if (!in_array($localeName, $availableLocaleNames, true)) {
                $errors[] = sprintf(static::ERROR_LOCALE_UNKNOWN, $localeName);

                continue;
            }

            $providedLocaleNames[] = $localeName;

            $name = $localizedAttributeEntry[static::KEY_NAME] ?? null;
            if ($isNameRequired && ($name === null || trim((string)$name) === '')) {
                $errors[] = sprintf(static::ERROR_LOCALIZED_NAME_MISSING, $localeName);
            }
        }

        if ($isNameRequired) {
            $missingLocaleNames = array_diff($availableLocaleNames, $providedLocaleNames);
            if ($missingLocaleNames !== []) {
                $errors[] = sprintf(static::ERROR_LOCALIZED_NAME_MISSING_FOR_LOCALES, implode(', ', $missingLocaleNames));
            }
        }

        return $errors;
    }

    /**
     * @param array<string> $storeNames
     *
     * @return array<string>
     */
    protected function validateStores(array $storeNames, CategoryWriteContextTransfer $categoryWriteContextTransfer): array
    {
        if ($storeNames === []) {
            return [];
        }

        $resolvedStoreNames = [];
        foreach ($categoryWriteContextTransfer->getStores() as $storeTransfer) {
            $resolvedStoreNames[] = $storeTransfer->getNameOrFail();
        }

        $errors = [];
        foreach (array_diff(array_values(array_unique($storeNames)), $resolvedStoreNames) as $unknownStoreName) {
            $errors[] = sprintf(static::ERROR_STORE_NOT_FOUND, $unknownStoreName);
        }

        return $errors;
    }

    /**
     * @param array<array<string, mixed>|object> $localizedAttributeEntries
     *
     * @return array<string>
     */
    protected function validateSiblingNames(
        array $localizedAttributeEntries,
        ?NodeTransfer $parentNodeTransfer,
        ?int $idCategory
    ): array {
        $errors = [];
        $categoryTransfer = (new CategoryTransfer())
            ->setIdCategory($idCategory)
            ->setParentCategoryNode($parentNodeTransfer);

        $checkedNames = [];
        foreach ($localizedAttributeEntries as $localizedAttributeEntry) {
            $localizedAttributeEntry = $this->normalizeEntry($localizedAttributeEntry);
            $name = $localizedAttributeEntry[static::KEY_NAME] ?? null;

            if ($name === null || $name === '' || isset($checkedNames[$name])) {
                continue;
            }

            $checkedNames[$name] = true;

            if ($this->categoryFacade->checkSameLevelCategoryByNameExists((string)$name, $categoryTransfer)) {
                $errors[] = sprintf(static::ERROR_SIBLING_NAME_EXISTS, $name);
            }
        }

        return $errors;
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
