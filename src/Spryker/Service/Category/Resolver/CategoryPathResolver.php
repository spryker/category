<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\Category\Resolver;

use Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer;
use Generated\Shared\Transfer\CategoryPathNodeTransfer;

class CategoryPathResolver implements CategoryPathResolverInterface
{
    /**
     * @return list<string>
     */
    public function resolveCategoryPath(CategoryPathNodeCollectionTransfer $categoryPathNodeCollectionTransfer): array
    {
        $categoryPathNodeTransfers = $categoryPathNodeCollectionTransfer->getNodes()->getArrayCopy();
        $categoryPathNodeTransfersByIdCategory = $this->getCategoryPathNodeTransfersIndexedByIdCategory($categoryPathNodeTransfers);
        $ascendantsByIdCategory = [];

        foreach ($categoryPathNodeTransfers as $categoryPathNodeTransfer) {
            $ascendantsByIdCategory[$categoryPathNodeTransfer->getIdCategoryOrFail()] = $this->getAscendantCategoryPathNodeTransfers(
                $categoryPathNodeTransfer,
                $categoryPathNodeTransfersByIdCategory,
            );
        }

        $categoryPathNodeTransfer = $this->findCategoryPathNodeTransferForCategoryPath(
            $categoryPathNodeTransfers,
            $ascendantsByIdCategory,
        );

        if ($categoryPathNodeTransfer === null) {
            return [];
        }

        return $this->getCategoryPath(
            $categoryPathNodeTransfer,
            $ascendantsByIdCategory[$categoryPathNodeTransfer->getIdCategoryOrFail()],
        );
    }

    /**
     * The ascendants of a category are the ascendants of its parent, followed by the parent itself, so the list starts at the top level.
     *
     * @param array<int, \Generated\Shared\Transfer\CategoryPathNodeTransfer> $categoryPathNodeTransfersByIdCategory
     * @param list<int> $chainCategoryIds The categories below this one in the chain.
     *
     * @return list<\Generated\Shared\Transfer\CategoryPathNodeTransfer>
     */
    protected function getAscendantCategoryPathNodeTransfers(
        CategoryPathNodeTransfer $categoryPathNodeTransfer,
        array $categoryPathNodeTransfersByIdCategory,
        array $chainCategoryIds = []
    ): array {
        $chainCategoryIds[] = $categoryPathNodeTransfer->getIdCategoryOrFail();

        $parentCategoryPathNodeTransfer = $this->findParentCategoryPathNodeTransfer(
            $categoryPathNodeTransfer,
            $categoryPathNodeTransfersByIdCategory,
        );

        if ($parentCategoryPathNodeTransfer === null) {
            return [];
        }

        // The parent is already in the chain: the parent IDs form a cycle, and following it would never end.
        if (in_array($parentCategoryPathNodeTransfer->getIdCategoryOrFail(), $chainCategoryIds, true)) {
            return [];
        }

        $ascendantCategoryPathNodeTransfers = $this->getAscendantCategoryPathNodeTransfers(
            $parentCategoryPathNodeTransfer,
            $categoryPathNodeTransfersByIdCategory,
            $chainCategoryIds,
        );
        $ascendantCategoryPathNodeTransfers[] = $parentCategoryPathNodeTransfer;

        return $ascendantCategoryPathNodeTransfers;
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CategoryPathNodeTransfer> $categoryPathNodeTransfersByIdCategory
     */
    protected function findParentCategoryPathNodeTransfer(
        CategoryPathNodeTransfer $categoryPathNodeTransfer,
        array $categoryPathNodeTransfersByIdCategory
    ): ?CategoryPathNodeTransfer {
        foreach ($categoryPathNodeTransfer->getParentCategoryIds() as $idParentCategory) {
            if (!isset($categoryPathNodeTransfersByIdCategory[$idParentCategory])) {
                continue;
            }

            return $categoryPathNodeTransfersByIdCategory[$idParentCategory];
        }

        return null;
    }

    /**
     * @param array<\Generated\Shared\Transfer\CategoryPathNodeTransfer> $categoryPathNodeTransfers
     * @param array<int, list<\Generated\Shared\Transfer\CategoryPathNodeTransfer>> $ascendantsByIdCategory
     */
    protected function findCategoryPathNodeTransferForCategoryPath(
        array $categoryPathNodeTransfers,
        array $ascendantsByIdCategory
    ): ?CategoryPathNodeTransfer {
        $selectedCategoryPathNodeTransfer = null;

        foreach ($categoryPathNodeTransfers as $candidateCategoryPathNodeTransfer) {
            if ($selectedCategoryPathNodeTransfer === null) {
                $selectedCategoryPathNodeTransfer = $candidateCategoryPathNodeTransfer;

                continue;
            }

            if ($this->isPreferredForCategoryPath($candidateCategoryPathNodeTransfer, $selectedCategoryPathNodeTransfer, $ascendantsByIdCategory)) {
                $selectedCategoryPathNodeTransfer = $candidateCategoryPathNodeTransfer;
            }
        }

        return $selectedCategoryPathNodeTransfer;
    }

    /**
     * @param array<int, list<\Generated\Shared\Transfer\CategoryPathNodeTransfer>> $ascendantsByIdCategory
     */
    protected function isPreferredForCategoryPath(
        CategoryPathNodeTransfer $candidateCategoryPathNodeTransfer,
        CategoryPathNodeTransfer $selectedCategoryPathNodeTransfer,
        array $ascendantsByIdCategory
    ): bool {
        $idCandidateCategory = $candidateCategoryPathNodeTransfer->getIdCategoryOrFail();
        $idSelectedCategory = $selectedCategoryPathNodeTransfer->getIdCategoryOrFail();
        $candidateDepth = count($ascendantsByIdCategory[$idCandidateCategory]);
        $selectedDepth = count($ascendantsByIdCategory[$idSelectedCategory]);

        if ($candidateDepth > $selectedDepth) {
            return true;
        }

        if ($candidateDepth < $selectedDepth) {
            return false;
        }

        // Equally deep: the lower category ID wins, so the pick does not depend on the order of the nodes.
        return $idCandidateCategory < $idSelectedCategory;
    }

    /**
     * @param list<\Generated\Shared\Transfer\CategoryPathNodeTransfer> $ascendantCategoryPathNodeTransfers
     *
     * @return list<string>
     */
    protected function getCategoryPath(
        CategoryPathNodeTransfer $categoryPathNodeTransfer,
        array $ascendantCategoryPathNodeTransfers
    ): array {
        $pathCategoryPathNodeTransfers = $ascendantCategoryPathNodeTransfers;
        $pathCategoryPathNodeTransfers[] = $categoryPathNodeTransfer;

        $categoryPath = [];

        foreach ($pathCategoryPathNodeTransfers as $pathCategoryPathNodeTransfer) {
            $name = $pathCategoryPathNodeTransfer->getName();

            if (!$name) {
                continue;
            }

            $categoryPath[] = $name;
        }

        return $categoryPath;
    }

    /**
     * @param array<\Generated\Shared\Transfer\CategoryPathNodeTransfer> $categoryPathNodeTransfers
     *
     * @return array<int, \Generated\Shared\Transfer\CategoryPathNodeTransfer>
     */
    protected function getCategoryPathNodeTransfersIndexedByIdCategory(array $categoryPathNodeTransfers): array
    {
        $categoryPathNodeTransfersByIdCategory = [];

        foreach ($categoryPathNodeTransfers as $categoryPathNodeTransfer) {
            $categoryPathNodeTransfersByIdCategory[$categoryPathNodeTransfer->getIdCategoryOrFail()] = $categoryPathNodeTransfer;
        }

        return $categoryPathNodeTransfersByIdCategory;
    }
}
