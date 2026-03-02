<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Creator;

use Generated\Shared\Transfer\CategoryClosureTableCollectionRequestTransfer;
use Generated\Shared\Transfer\CategoryClosureTableCollectionResponseTransfer;
use Generated\Shared\Transfer\NodeTransfer;

interface CategoryClosureTableCreatorInterface
{
    public function createCategoryClosureTable(NodeTransfer $nodeTransfer): void;

    public function createCategoryClosureTableCollection(
        CategoryClosureTableCollectionRequestTransfer $categoryClosureTableCollectionRequestTransfer
    ): CategoryClosureTableCollectionResponseTransfer;
}
