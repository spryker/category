<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Deleter;

interface CategoryNodeDeleterInterface
{
    public function deleteCategoryNodesForCategory(int $idCategory): void;

    public function deleteCategoryExtraParentNodesForCategory(int $idCategory): void;

    /**
     * @param array<\Generated\Shared\Transfer\NodeTransfer> $nodeTransfers
     *
     * @return void
     */
    public function deleteCategoryNodes(array $nodeTransfers): void;
}
