<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Mapper;

use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\CategoryWriteContextTransfer;

interface CategoryRequestMapperInterface
{
    public function mapResourceToNewCategoryTransfer(
        CategoriesBackendResource $categoriesBackendResource,
        CategoryWriteContextTransfer $categoryWriteContextTransfer
    ): CategoryTransfer;

    public function mapResourceToExistingCategoryTransfer(
        CategoriesBackendResource $categoriesBackendResource,
        CategoryTransfer $categoryTransfer,
        CategoryWriteContextTransfer $categoryWriteContextTransfer
    ): CategoryTransfer;
}
