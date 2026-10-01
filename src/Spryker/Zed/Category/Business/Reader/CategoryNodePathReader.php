<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Reader;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Category\CategoryConfig;
use Spryker\Zed\Category\Persistence\CategoryRepositoryInterface;

class CategoryNodePathReader implements CategoryNodePathReaderInterface
{
    public function __construct(
        protected CategoryRepositoryInterface $categoryRepository,
        protected CategoryConfig $categoryConfig
    ) {
    }

    /**
     * @param array<int> $categoryNodeIds
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return array<int, string>
     */
    public function getNodePathsIndexedByIdCategoryNode(array $categoryNodeIds, LocaleTransfer $localeTransfer): array
    {
        $nodePathsIndexedByIdCategoryNode = [];
        foreach (array_chunk($categoryNodeIds, $this->categoryConfig->getBatchReadChunkSize()) as $categoryNodeIdsChunk) {
            $nodePathsIndexedByIdCategoryNode += $this->categoryRepository->getNodePathsIndexedByIdCategoryNode($categoryNodeIdsChunk, $localeTransfer);
        }

        return $nodePathsIndexedByIdCategoryNode;
    }
}
