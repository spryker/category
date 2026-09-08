<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Reader;

use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryWriteContextTransfer;

interface CategoryWriteContextReaderInterface
{
    public function getCategoryWriteContext(CategoriesBackendResource $categoriesBackendResource): CategoryWriteContextTransfer;
}
