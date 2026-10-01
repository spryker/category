<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category\Persistence;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Orm\Zed\Category\Persistence\Map\SpyCategoryTableMap;
use Propel\Runtime\Propel;
use Spryker\Zed\Category\Persistence\CategoryRepository;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Category
 * @group Persistence
 * @group GetNodePathsIndexedByIdCategoryNodeTest
 * Add your own group annotations below this line
 */
class GetNodePathsIndexedByIdCategoryNodeTest extends Unit
{
    protected const int NESTED_CATEGORY_COUNT = 3;

    /**
     * @var \SprykerTest\Zed\Category\CategoryPersistenceTester
     */
    protected $tester;

    public function testLoadsNodePathsOfAllGivenNodesWithOneQuery(): void
    {
        // Arrange
        $localeTransfer = $this->getCurrentLocale();
        $categoryNodeIds = $this->haveNestedCategoryNodeIds(static::NESTED_CATEGORY_COUNT);

        // Act
        $queryCount = $this->countNodePathQueries(new CategoryRepository(), $categoryNodeIds, $localeTransfer);

        // Assert
        $this->assertSame(1, $queryCount);
    }

    public function testReturnsSameNodePathsAsSingleNodeLookup(): void
    {
        // Arrange
        $localeTransfer = $this->getCurrentLocale();
        $categoryNodeIds = $this->haveNestedCategoryNodeIds(static::NESTED_CATEGORY_COUNT);
        $categoryRepository = new CategoryRepository();

        // Act
        $nodePathsIndexedByIdCategoryNode = $categoryRepository->getNodePathsIndexedByIdCategoryNode($categoryNodeIds, $localeTransfer);

        // Assert
        $this->assertSame($categoryNodeIds, array_keys($nodePathsIndexedByIdCategoryNode));

        foreach ($categoryNodeIds as $idCategoryNode) {
            $this->assertSame(
                $categoryRepository->getNodePath($idCategoryNode, $localeTransfer),
                $nodePathsIndexedByIdCategoryNode[$idCategoryNode],
            );
        }
    }

    /**
     * @return array<int>
     */
    protected function haveNestedCategoryNodeIds(int $categoryCount): array
    {
        $categoryNodeIds = [];
        $parentCategoryNodeTransfer = null;
        for ($i = 0; $i < $categoryCount; $i++) {
            $seedData = $parentCategoryNodeTransfer ? [CategoryTransfer::PARENT_CATEGORY_NODE => $parentCategoryNodeTransfer] : [];
            $parentCategoryNodeTransfer = $this->tester->haveLocalizedCategory($seedData)->getCategoryNodeOrFail();
            $categoryNodeIds[] = $parentCategoryNodeTransfer->getIdCategoryNodeOrFail();
        }

        return $categoryNodeIds;
    }

    /**
     * @param array<int> $categoryNodeIds
     */
    protected function countNodePathQueries(CategoryRepository $categoryRepository, array $categoryNodeIds, LocaleTransfer $localeTransfer): int
    {
        /** @var \Propel\Runtime\Connection\ConnectionWrapper $connection */
        $connection = Propel::getWriteConnection(SpyCategoryTableMap::DATABASE_NAME);

        $connection->useDebug(false);
        $connection->useDebug(true);

        try {
            $categoryRepository->getNodePathsIndexedByIdCategoryNode($categoryNodeIds, $localeTransfer);

            return $connection->getQueryCount();
        } finally {
            $connection->useDebug(false);
        }
    }

    protected function getCurrentLocale(): LocaleTransfer
    {
        return $this->tester->getLocator()->locale()->facade()->getCurrentLocale();
    }
}
