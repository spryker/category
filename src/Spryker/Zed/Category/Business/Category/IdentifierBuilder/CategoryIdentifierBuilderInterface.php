<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Category\IdentifierBuilder;

use Generated\Shared\Transfer\CategoryTransfer;

interface CategoryIdentifierBuilderInterface
{
    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return string
     */
    public function buildIdentifier(CategoryTransfer $categoryTransfer): string;
}
