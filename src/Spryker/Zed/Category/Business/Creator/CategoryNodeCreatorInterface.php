<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Creator;

use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeTransfer;

interface CategoryNodeCreatorInterface
{
    public function createCategoryNode(CategoryTransfer $categoryTransfer): void;

    public function createExtraParentsCategoryNodes(CategoryTransfer $categoryTransfer): void;

    public function addExtraParentCategoryNodeToCategory(CategoryTransfer $categoryTransfer, NodeTransfer $extraParentNodeTransfer): void;
}
