<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence;

use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;

interface CategoryRepositoryInterface
{
    public const NODE_PATH_GLUE = '/';
    public const CATEGORY_NODE_PATH_GLUE = ' / ';
    public const EXCLUDE_NODE_PATH_ROOT = true;
    public const NODE_PATH_NULL_DEPTH = null;
    public const NODE_PATH_ZERO_DEPTH = 0;
    public const IS_ROOT_NODE = 0;

    /**
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionTransfer
     */
    public function getAllCategoryCollection(LocaleTransfer $localeTransfer): CategoryCollectionTransfer;

    /**
     * @param int $idNode
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return string
     */
    public function getNodePath(int $idNode, LocaleTransfer $localeTransfer);

    /**
     * @param int $idNode
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     * @param string $glue
     * @param bool $excludeRoot
     * @param int|null $depth
     *
     * @return string
     */
    public function getCategoryNodePath(
        int $idNode,
        LocaleTransfer $localeTransfer,
        string $glue = self::CATEGORY_NODE_PATH_GLUE,
        bool $excludeRoot = self::EXCLUDE_NODE_PATH_ROOT,
        ?int $depth = self::NODE_PATH_NULL_DEPTH
    ): string;

    /**
     * @param string $nodeName
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return bool
     */
    public function checkSameLevelCategoryByNameExists(string $nodeName, CategoryTransfer $categoryTransfer): bool;

    /**
     * @param int $idCategory
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer|null
     */
    public function findCategoryById(int $idCategory): ?CategoryTransfer;
}
