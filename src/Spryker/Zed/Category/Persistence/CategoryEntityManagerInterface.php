<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence;

use Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeTransfer;

interface CategoryEntityManagerInterface
{
    public function createCategory(CategoryTransfer $categoryTransfer): CategoryTransfer;

    /**
     * @param array<int> $categoryIds
     * @param array<int> $storeIds
     *
     * @return void
     */
    public function bulkCreateCategoryStoreRelationForStores(array $categoryIds, array $storeIds): void;

    public function createCategoryNode(NodeTransfer $nodeTransfer): NodeTransfer;

    public function createCategoryClosureTableRootNode(NodeTransfer $nodeTransfer): void;

    public function createCategoryClosureTableNodes(NodeTransfer $nodeTransfer): void;

    public function createCategoryClosureTableParentEntriesForCategoryNode(NodeTransfer $nodeTransfer): void;

    public function saveCategoryAttribute(int $idCategory, CategoryLocalizedAttributesTransfer $categoryLocalizedAttributesTransfer): void;

    public function saveCategoryExtraParentNode(CategoryTransfer $categoryTransfer, NodeTransfer $extraParentNodeTransfer): NodeTransfer;

    public function updateCategory(CategoryTransfer $categoryTransfer): void;

    public function updateCategoryNode(NodeTransfer $nodeTransfer): NodeTransfer;

    public function deleteCategory(int $idCategory): void;

    public function deleteCategoryLocalizedAttributes(int $idCategory): void;

    public function deleteCategoryNode(int $idCategoryNode): void;

    public function deleteCategoryClosureTable(int $idCategoryNode): void;

    public function deleteCategoryStoreRelations(int $idCategory): void;

    public function deleteCategoryClosureTableParentEntriesForCategoryNode(int $idCategoryNode): void;

    /**
     * @param array<int> $categoryIds
     * @param array<int> $storeIds
     *
     * @return void
     */
    public function bulkDeleteCategoryStoreRelationForStores(array $categoryIds, array $storeIds): void;
}
