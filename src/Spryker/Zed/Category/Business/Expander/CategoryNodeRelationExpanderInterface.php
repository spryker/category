<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Expander;

use Generated\Shared\Transfer\NodeCollectionTransfer;

interface CategoryNodeRelationExpanderInterface
{
    public function expandNodeCollectionWithRelations(
        NodeCollectionTransfer $categoryNodeCollectionTransfer
    ): NodeCollectionTransfer;
}
