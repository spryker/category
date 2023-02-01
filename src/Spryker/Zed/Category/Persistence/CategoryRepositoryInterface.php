<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence;

use Generated\Shared\Transfer\CategoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\CategoryNodeCriteriaTransfer;
use Generated\Shared\Transfer\CategoryNodeUrlCriteriaTransfer;
use Generated\Shared\Transfer\CategoryNodeUrlPathCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTemplateTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\NodeCollectionTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;

interface CategoryRepositoryInterface
{
    /**
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionTransfer
     */
    public function getAllCategoryCollection(LocaleTransfer $localeTransfer): CategoryCollectionTransfer;

    /**
     * @deprecated Use {@link \Spryker\Zed\Category\Persistence\CategoryRepositoryInterface::getCategoryCollection()} instead.
     *
     * @param \Generated\Shared\Transfer\CategoryCriteriaTransfer $categoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionTransfer
     */
    public function getCategoriesByCriteria(CategoryCriteriaTransfer $categoryCriteriaTransfer): CategoryCollectionTransfer;

    /**
     * @param int $idCategoryNode
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return string
     */
    public function getNodePath(int $idCategoryNode, LocaleTransfer $localeTransfer);

    /**
     * @param int $idNode
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return string
     */
    public function getCategoryNodePath(int $idNode, LocaleTransfer $localeTransfer): string;

    /**
     * @param string $nodeName
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return bool
     */
    public function checkSameLevelCategoryByNameExists(string $nodeName, CategoryTransfer $categoryTransfer): bool;

    /**
     * @param int $idCategory
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer|null
     */
    public function findCategoryById(int $idCategory): ?CategoryTransfer;

    /**
     * @param int $idCategoryNode
     *
     * @return array<int>
     */
    public function getChildCategoryNodeIdsByCategoryNodeId(int $idCategoryNode): array;

    /**
     * @param int $idCategoryNode
     *
     * @return array<int>
     */
    public function getParentCategoryNodeIdsByCategoryNodeId(int $idCategoryNode): array;

    /**
     * @param \Generated\Shared\Transfer\CategoryCriteriaTransfer $categoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer|null
     */
    public function findCategoryByCriteria(CategoryCriteriaTransfer $categoryCriteriaTransfer): ?CategoryTransfer;

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param \Generated\Shared\Transfer\CategoryCriteriaTransfer $categoryCriteriaTransfer
     *
     * @return array<array<\Generated\Shared\Transfer\NodeTransfer>>
     */
    public function getCategoryNodeChildNodesCollectionIndexedByParentNodeId(
        CategoryTransfer $categoryTransfer,
        CategoryCriteriaTransfer $categoryCriteriaTransfer
    ): array;

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return int
     */
    public function getCategoryNodeChildCountByParentNodeId(
        CategoryTransfer $categoryTransfer
    ): int;

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param \Generated\Shared\Transfer\CategoryCriteriaTransfer $categoryCriteriaTransfer
     *
     * @return array<int>
     */
    public function getDescendantCategoryIdsByIdCategory(
        CategoryTransfer $categoryTransfer,
        CategoryCriteriaTransfer $categoryCriteriaTransfer
    ): array;

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param \Generated\Shared\Transfer\CategoryCriteriaTransfer $categoryCriteriaTransfer
     *
     * @return array<int>
     */
    public function getDescendantCategoryNodeIdsByIdCategory(
        CategoryTransfer $categoryTransfer,
        CategoryCriteriaTransfer $categoryCriteriaTransfer
    ): array;

    /**
     * @param \Generated\Shared\Transfer\CategoryNodeUrlCriteriaTransfer $categoryNodeUrlCriteriaTransfer
     *
     * @return array<\Generated\Shared\Transfer\UrlTransfer>
     */
    public function getCategoryNodeUrls(CategoryNodeUrlCriteriaTransfer $categoryNodeUrlCriteriaTransfer): array;

    /**
     * @param \Generated\Shared\Transfer\CategoryNodeUrlPathCriteriaTransfer $categoryNodeUrlPathCriteriaTransfer
     *
     * @return array
     */
    public function getCategoryNodeUrlPathParts(CategoryNodeUrlPathCriteriaTransfer $categoryNodeUrlPathCriteriaTransfer): array;

    /**
     * @param \Generated\Shared\Transfer\CategoryNodeUrlPathCriteriaTransfer $categoryNodeUrlPathCriteriaTransfer
     *
     * @return array
     */
    public function getBulkCategoryNodeUrlPathParts(CategoryNodeUrlPathCriteriaTransfer $categoryNodeUrlPathCriteriaTransfer): array;

    /**
     * @param \Generated\Shared\Transfer\CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\NodeCollectionTransfer
     */
    public function getCategoryNodesWithRelativeNodes(
        CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer
    ): NodeCollectionTransfer;

    /**
     * @param \Generated\Shared\Transfer\CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\NodeCollectionTransfer
     */
    public function getCategoryNodes(CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer): NodeCollectionTransfer;

    /**
     * @param int $idCategoryNode
     *
     * @return \Generated\Shared\Transfer\StoreRelationTransfer
     */
    public function getCategoryStoreRelationByIdCategoryNode(int $idCategoryNode): StoreRelationTransfer;

    /**
     * @param int $idCategoryNode
     *
     * @return \Generated\Shared\Transfer\NodeTransfer|null
     */
    public function findCategoryNodeByIdCategoryNode(int $idCategoryNode): ?NodeTransfer;

    /**
     * @param \Generated\Shared\Transfer\CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer
     *
     * @return array<int, array<string, string>>
     */
    public function getAscendantCategoryKeys(CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer): array;

    /**
     * @param array<int> $categoryIds
     *
     * @return array<int, array<\Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer>>
     */
    public function getCategoryAttributesByCategoryIdsGroupByIdCategory(array $categoryIds): array;

    /**
     * @param array<int> $categoryIds
     *
     * @return array<\Generated\Shared\Transfer\StoreRelationTransfer>
     */
    public function getCategoryStoreRelationsByCategoryIds(array $categoryIds): array;

    /**
     * @param \Generated\Shared\Transfer\CategoryCriteriaTransfer $categoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionTransfer
     */
    public function getCategoryCollection(
        CategoryCriteriaTransfer $categoryCriteriaTransfer
    ): CategoryCollectionTransfer;

    /**
     * @param \Generated\Shared\Transfer\CategoryCollectionDeleteCriteriaTransfer $categoryCollectionDeleteCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionTransfer
     */
    public function getCategoryDeleteCollection(
        CategoryCollectionDeleteCriteriaTransfer $categoryCollectionDeleteCriteriaTransfer
    ): CategoryCollectionTransfer;

    /**
     * @return \Generated\Shared\Transfer\CategoryTemplateTransfer
     */
    public function getDefaultCategoryTemplate(): CategoryTemplateTransfer;
}
