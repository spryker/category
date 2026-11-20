<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\Category\Resolver;

use Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer;

interface CategoryPathResolverInterface
{
    /**
     * @return list<string>
     */
    public function resolveCategoryPath(CategoryPathNodeCollectionTransfer $categoryPathNodeCollectionTransfer): array;
}
