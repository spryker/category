<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Orm\Zed\Category\Persistence\Map\SpyCategoryTableMap;
use Propel\Runtime\Propel;
use Spryker\Zed\Category\Persistence\CategoryRepository;
use SprykerTest\Zed\Category\CategoryBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Category
 * @group Business
 * @group Facade
 * @group GetAllCategoryCollectionTest
 * Add your own group annotations below this line
 */
class GetAllCategoryCollectionTest extends Unit
{
    protected const int ADDITIONAL_CATEGORY_COUNT = 5;

    /**
     * @var \SprykerTest\Zed\Category\CategoryBusinessTester
     */
    protected CategoryBusinessTester $tester;

    public function testReturnsNodePathsEqualToSingleNodePathLookup(): void
    {
        // Arrange
        $localeTransfer = $this->getCurrentLocale();
        $parentCategoryTransfer = $this->tester->haveLocalizedCategory();
        $childCategoryTransfer = $this->tester->haveLocalizedCategory([
            CategoryTransfer::PARENT_CATEGORY_NODE => $parentCategoryTransfer->getCategoryNodeOrFail(),
        ]);

        // Act
        $categoryCollectionTransfer = $this->tester->getFacade()->getAllCategoryCollection($localeTransfer);

        // Assert
        $childNodeTransfer = $this->findNodeTransfer($categoryCollectionTransfer, $childCategoryTransfer->getCategoryNodeOrFail()->getIdCategoryNodeOrFail());
        $this->assertNotNull($childNodeTransfer);
        $this->assertStringEndsWith(
            $this->getLocalizedName($parentCategoryTransfer, $localeTransfer),
            $childNodeTransfer->getPathOrFail(),
        );

        $categoryRepository = new CategoryRepository();
        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            foreach ($categoryTransfer->getNodeCollectionOrFail()->getNodes() as $nodeTransfer) {
                $this->assertSame(
                    $categoryRepository->getNodePath($nodeTransfer->getIdCategoryNodeOrFail(), $localeTransfer),
                    $nodeTransfer->getPath(),
                );
            }
        }
    }

    public function testQueryCountDoesNotGrowWithNumberOfCategoryNodes(): void
    {
        // Arrange
        $localeTransfer = $this->getCurrentLocale();
        $smallQueryCount = $this->countGetAllCategoryCollectionQueries($localeTransfer);

        $parentCategoryNodeTransfer = $this->tester->haveLocalizedCategory()->getCategoryNodeOrFail();
        for ($i = 1; $i < static::ADDITIONAL_CATEGORY_COUNT; $i++) {
            $parentCategoryNodeTransfer = $this->tester->haveLocalizedCategory([
                CategoryTransfer::PARENT_CATEGORY_NODE => $parentCategoryNodeTransfer,
            ])->getCategoryNodeOrFail();
        }

        // Act
        $largeQueryCount = $this->countGetAllCategoryCollectionQueries($localeTransfer);

        // Assert
        $this->assertGreaterThan(0, $smallQueryCount, 'No queries were counted, so the growth assertion would hold vacuously.');
        $this->assertSame(
            $smallQueryCount,
            $largeQueryCount,
            sprintf(
                'Adding %d category nodes raised the query count from %d to %d; category data is loaded per node.',
                static::ADDITIONAL_CATEGORY_COUNT,
                $smallQueryCount,
                $largeQueryCount,
            ),
        );
    }

    protected function countGetAllCategoryCollectionQueries(LocaleTransfer $localeTransfer): int
    {
        /** @var \Propel\Runtime\Connection\ConnectionWrapper $connection */
        $connection = Propel::getWriteConnection(SpyCategoryTableMap::DATABASE_NAME);

        $connection->useDebug(false);
        $connection->useDebug(true);

        try {
            $this->tester->getFacade()->getAllCategoryCollection($localeTransfer);

            return $connection->getQueryCount();
        } finally {
            $connection->useDebug(false);
        }
    }

    protected function getCurrentLocale(): LocaleTransfer
    {
        return $this->tester->getLocator()->locale()->facade()->getCurrentLocale();
    }

    protected function findNodeTransfer(CategoryCollectionTransfer $categoryCollectionTransfer, int $idCategoryNode): ?NodeTransfer
    {
        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransfer) {
            foreach ($categoryTransfer->getNodeCollectionOrFail()->getNodes() as $nodeTransfer) {
                if ($nodeTransfer->getIdCategoryNode() === $idCategoryNode) {
                    return $nodeTransfer;
                }
            }
        }

        return null;
    }

    protected function getLocalizedName(CategoryTransfer $categoryTransfer, LocaleTransfer $localeTransfer): string
    {
        foreach ($categoryTransfer->getLocalizedAttributes() as $categoryLocalizedAttributesTransfer) {
            if ($categoryLocalizedAttributesTransfer->getLocaleOrFail()->getIdLocale() === $localeTransfer->getIdLocale()) {
                return $categoryLocalizedAttributesTransfer->getNameOrFail();
            }
        }

        return '';
    }
}
