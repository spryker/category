<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Reader;

use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\PaginationTransfer;

interface CategoryReaderInterface
{
    public function findCategoryTransferByCategoryKey(string $categoryKey): ?CategoryTransfer;

    public function findCategoryResourceByCategoryKey(string $categoryKey): ?CategoriesBackendResource;

    /**
     * @param array<\Generated\Shared\Transfer\SortTransfer> $sortTransfers
     * @param array<int>|null $categoryIds
     *
     * @return array<\Generated\Api\Backend\CategoriesBackendResource>
     */
    public function getCategoryResourceCollection(
        PaginationTransfer $paginationTransfer,
        array $sortTransfers = [],
        ?array $categoryIds = null,
        ?LocaleTransfer $localeTransfer = null
    ): array;

    /**
     * @return array<int>
     */
    public function getChildCategoryIdsByIdCategoryNode(int $idCategoryNode): array;
}
