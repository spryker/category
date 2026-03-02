<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Category\Validator;

use Generated\Shared\Transfer\CategoryCollectionResponseTransfer;
use Generated\Shared\Transfer\CategoryTransfer;

interface CategoryValidatorInterface
{
    public function validateCollection(
        CategoryCollectionResponseTransfer $categoryCollectionResponseTransfer
    ): CategoryCollectionResponseTransfer;

    public function validate(
        CategoryTransfer $categoryTransfer,
        CategoryCollectionResponseTransfer $categoryCollectionResponseTransfer
    ): CategoryCollectionResponseTransfer;
}
