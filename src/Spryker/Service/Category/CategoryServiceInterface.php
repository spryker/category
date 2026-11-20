<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\Category;

use Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer;

interface CategoryServiceInterface
{
    /**
     * Specification:
     * - Resolves the category path of a product from its categories and the categories above them in `CategoryPathNodeCollectionTransfer.nodes`.
     * - Follows `CategoryPathNode.parentCategoryIds` upwards; a category whose parents are not among the nodes is top level, so a root category left out of the nodes never appears.
     * - Takes the deepest category; among equally deep categories the lowest `CategoryPathNode.idCategory`.
     * - Stops following the parents at a category that is already in the path, so parent IDs that form a cycle do not loop forever.
     * - Returns the names of that category and the categories above it, top level first, e.g. ["Computers", "Notebooks"]; empty names are left out.
     * - Returns an empty array without nodes.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer $categoryPathNodeCollectionTransfer
     *
     * @return list<string>
     */
    public function getCategoryPath(CategoryPathNodeCollectionTransfer $categoryPathNodeCollectionTransfer): array;
}
