<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Reader;

use Generated\Shared\Transfer\LocaleTransfer;

interface CategoryNodePathReaderInterface
{
    /**
     * @param array<int> $categoryNodeIds
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return array<int, string>
     */
    public function getNodePathsIndexedByIdCategoryNode(array $categoryNodeIds, LocaleTransfer $localeTransfer): array;
}
