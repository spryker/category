<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Reorderer;

use Generated\Shared\Transfer\CategoryNodeCollectionRequestTransfer;
use Generated\Shared\Transfer\CategoryNodeCollectionResponseTransfer;

interface CategoryNodeReordererInterface
{
    public function reorderCategoryNodeCollection(
        CategoryNodeCollectionRequestTransfer $categoryNodeCollectionRequestTransfer
    ): CategoryNodeCollectionResponseTransfer;
}
