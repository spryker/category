<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Deleter;

use Generated\Shared\Transfer\CategoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\CategoryCollectionResponseTransfer;

interface CategoryDeleterInterface
{
    public function deleteCategory(int $idCategory): void;

    public function deleteCategoryCollection(
        CategoryCollectionDeleteCriteriaTransfer $categoryCollectionDeleteCriteriaTransfer
    ): CategoryCollectionResponseTransfer;
}
