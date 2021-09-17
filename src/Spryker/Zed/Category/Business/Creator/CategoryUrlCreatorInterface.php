<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Creator;

use ArrayObject;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeTransfer;

interface CategoryUrlCreatorInterface
{
    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    public function createCategoryUrl(CategoryTransfer $categoryTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\NodeTransfer $nodeTransfer
     * @param \ArrayObject<int, \Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer> $categoryLocalizedAttributesTransfers
     *
     * @return void
     */
    public function createLocalizedCategoryUrlsForNode(NodeTransfer $nodeTransfer, ArrayObject $categoryLocalizedAttributesTransfers): void;
}
