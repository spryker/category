<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Mapper;

use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryTransfer;

interface CategoryResourceMapperInterface
{
    /**
     * @param array<int, string> $templateNamesIndexedByIdCategoryTemplate
     * @param array<int, string> $categoryKeysIndexedByIdCategory
     * @param array<string, string> $urlsIndexedByLocaleName
     */
    public function mapCategoryTransferToCategoriesBackendResource(
        CategoryTransfer $categoryTransfer,
        array $templateNamesIndexedByIdCategoryTemplate,
        array $categoryKeysIndexedByIdCategory,
        array $urlsIndexedByLocaleName
    ): CategoriesBackendResource;
}
