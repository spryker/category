<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Creator;

use Generated\Shared\Transfer\CategoryTransfer;

interface CategoryStoreCreatorInterface
{
    public function createCategoryStoreRelations(CategoryTransfer $categoryTransfer): void;
}
