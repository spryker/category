<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Category\Business\Product\Validator;

use Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionResponseTransfer;

interface ProductCategoryValidatorInterface
{
    public function validateProductAbstractCollection(
        ProductAbstractCollectionRequestTransfer $productAbstractCollectionRequestTransfer,
        ProductAbstractCollectionResponseTransfer $productAbstractCollectionResponseTransfer
    ): ProductAbstractCollectionResponseTransfer;
}
