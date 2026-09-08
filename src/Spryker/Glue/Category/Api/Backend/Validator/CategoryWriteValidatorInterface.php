<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Validator;

use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\CategoryWriteContextTransfer;

interface CategoryWriteValidatorInterface
{
    /**
     * @return array<string>
     */
    public function validateCreate(
        CategoriesBackendResource $categoriesBackendResource,
        CategoryWriteContextTransfer $categoryWriteContextTransfer
    ): array;

    /**
     * @return array<string>
     */
    public function validateUpdate(
        CategoriesBackendResource $categoriesBackendResource,
        CategoryTransfer $currentCategoryTransfer,
        CategoryWriteContextTransfer $categoryWriteContextTransfer
    ): array;

    /**
     * @return array<string>
     */
    public function validateDelete(CategoryTransfer $currentCategoryTransfer): array;
}
