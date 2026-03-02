<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Creator;

use Generated\Shared\Transfer\CategoryCollectionRequestTransfer;
use Generated\Shared\Transfer\CategoryCollectionResponseTransfer;
use Generated\Shared\Transfer\CategoryTransfer;

interface CategoryCreatorInterface
{
    public function createCategory(CategoryTransfer $categoryTransfer): void;

    public function createCategoryCollection(
        CategoryCollectionRequestTransfer $categoryCollectionRequestTransfer
    ): CategoryCollectionResponseTransfer;
}
