<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Model\Category;

use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Category\Business\Reader\CategoryNodePathReaderInterface;

class CategoryHydrator implements CategoryHydratorInterface
{
    public function __construct(protected CategoryNodePathReaderInterface $categoryNodePathReader)
    {
    }

    public function hydrateCategoryCollection(CategoryCollectionTransfer $categoryCollectionTransfer, LocaleTransfer $localeTransfer): void
    {
        $nodePathsIndexedByIdCategoryNode = $this->categoryNodePathReader->getNodePathsIndexedByIdCategoryNode(
            $this->extractCategoryNodeIds($categoryCollectionTransfer),
            $localeTransfer,
        );

        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            foreach ($categoryTransfer->getNodeCollectionOrFail()->getNodes() as $nodeTransfer) {
                $nodeTransfer->setPath($nodePathsIndexedByIdCategoryNode[$nodeTransfer->getIdCategoryNodeOrFail()]);
            }
        }
    }

    /**
     * @return array<int>
     */
    protected function extractCategoryNodeIds(CategoryCollectionTransfer $categoryCollectionTransfer): array
    {
        $categoryNodeIds = [];
        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            foreach ($categoryTransfer->getNodeCollectionOrFail()->getNodes() as $nodeTransfer) {
                $categoryNodeIds[] = $nodeTransfer->getIdCategoryNodeOrFail();
            }
        }

        return array_values(array_unique($categoryNodeIds));
    }
}
