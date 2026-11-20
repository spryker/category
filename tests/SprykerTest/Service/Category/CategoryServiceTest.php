<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Service\Category;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer;
use Generated\Shared\Transfer\CategoryPathNodeTransfer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Service
 * @group Category
 * @group CategoryServiceTest
 * Add your own group annotations below this line
 */
class CategoryServiceTest extends Unit
{
    protected const int ID_CATEGORY_COMPUTER = 2;

    protected const int ID_CATEGORY_OFFERS = 3;

    protected const int ID_CATEGORY_NOTEBOOKS = 4;

    protected const int ID_CATEGORY_WORKSTATIONS = 5;

    protected const string NAME_COMPUTER = 'Computer';

    protected const string NAME_NOTEBOOKS = 'Notebooks';

    protected const string NAME_OFFERS = 'Offers';

    protected const string NAME_WORKSTATIONS = 'Workstations';

    protected CategoryServiceTester $tester;

    public function testGetCategoryPathReturnsPathTopLevelFirst(): void
    {
        // Arrange
        $categoryPathNodeCollectionTransfer = $this->createCategoryPathNodeCollection([
            [static::ID_CATEGORY_COMPUTER, static::NAME_COMPUTER, []],
            [static::ID_CATEGORY_NOTEBOOKS, static::NAME_NOTEBOOKS, [static::ID_CATEGORY_COMPUTER]],
        ]);

        // Act
        $categoryPath = $this->tester->getLocator()->category()->service()->getCategoryPath($categoryPathNodeCollectionTransfer);

        // Assert
        $this->assertSame([static::NAME_COMPUTER, static::NAME_NOTEBOOKS], $categoryPath);
    }

    public function testGetCategoryPathFollowsTheWholeAscendantChain(): void
    {
        // Arrange
        $categoryPathNodeCollectionTransfer = $this->createCategoryPathNodeCollection([
            [static::ID_CATEGORY_WORKSTATIONS, static::NAME_WORKSTATIONS, [static::ID_CATEGORY_NOTEBOOKS]],
            [static::ID_CATEGORY_COMPUTER, static::NAME_COMPUTER, []],
            [static::ID_CATEGORY_NOTEBOOKS, static::NAME_NOTEBOOKS, [static::ID_CATEGORY_COMPUTER]],
        ]);

        // Act
        $categoryPath = $this->tester->getLocator()->category()->service()->getCategoryPath($categoryPathNodeCollectionTransfer);

        // Assert
        $this->assertSame([static::NAME_COMPUTER, static::NAME_NOTEBOOKS, static::NAME_WORKSTATIONS], $categoryPath);
    }

    public function testGetCategoryPathEndsTheChainAtACategoryWhoseParentIsNotANode(): void
    {
        // Arrange
        $categoryPathNodeCollectionTransfer = $this->createCategoryPathNodeCollection([
            [static::ID_CATEGORY_NOTEBOOKS, static::NAME_NOTEBOOKS, [static::ID_CATEGORY_COMPUTER]],
        ]);

        // Act
        $categoryPath = $this->tester->getLocator()->category()->service()->getCategoryPath($categoryPathNodeCollectionTransfer);

        // Assert
        $this->assertSame([static::NAME_NOTEBOOKS], $categoryPath);
    }

    public function testGetCategoryPathPicksDeepestCategoryOverOneWithLowerId(): void
    {
        // Arrange
        $categoryPathNodeCollectionTransfer = $this->createCategoryPathNodeCollection([
            [static::ID_CATEGORY_OFFERS, static::NAME_OFFERS, []],
            [static::ID_CATEGORY_COMPUTER, static::NAME_COMPUTER, []],
            [static::ID_CATEGORY_NOTEBOOKS, static::NAME_NOTEBOOKS, [static::ID_CATEGORY_COMPUTER]],
        ]);

        // Act
        $categoryPath = $this->tester->getLocator()->category()->service()->getCategoryPath($categoryPathNodeCollectionTransfer);

        // Assert
        $this->assertSame([static::NAME_COMPUTER, static::NAME_NOTEBOOKS], $categoryPath);
    }

    public function testGetCategoryPathPicksLowestIdAmongEquallyDeepCategories(): void
    {
        // Arrange
        $categoryPathNodeCollectionTransfer = $this->createCategoryPathNodeCollection([
            [static::ID_CATEGORY_WORKSTATIONS, static::NAME_WORKSTATIONS, [static::ID_CATEGORY_COMPUTER]],
            [static::ID_CATEGORY_COMPUTER, static::NAME_COMPUTER, []],
            [static::ID_CATEGORY_NOTEBOOKS, static::NAME_NOTEBOOKS, [static::ID_CATEGORY_COMPUTER]],
        ]);

        // Act
        $categoryPath = $this->tester->getLocator()->category()->service()->getCategoryPath($categoryPathNodeCollectionTransfer);

        // Assert
        $this->assertSame([static::NAME_COMPUTER, static::NAME_NOTEBOOKS], $categoryPath);
    }

    /**
     * A category tree cannot contain a cycle, but the parent ids are data: a cycle must not hang the path resolution.
     * Both categories are one level deep, so the lower category id wins and the path stops before repeating it.
     */
    public function testGetCategoryPathStopsAtTheFirstRepeatedCategoryOnCyclicParentIds(): void
    {
        // Arrange
        $categoryPathNodeCollectionTransfer = $this->createCategoryPathNodeCollection([
            [static::ID_CATEGORY_COMPUTER, static::NAME_COMPUTER, [static::ID_CATEGORY_NOTEBOOKS]],
            [static::ID_CATEGORY_NOTEBOOKS, static::NAME_NOTEBOOKS, [static::ID_CATEGORY_COMPUTER]],
        ]);

        // Act
        $categoryPath = $this->tester->getLocator()->category()->service()->getCategoryPath($categoryPathNodeCollectionTransfer);

        // Assert
        $this->assertSame([static::NAME_NOTEBOOKS, static::NAME_COMPUTER], $categoryPath);
    }

    public function testGetCategoryPathReturnsNoPathWithoutNodes(): void
    {
        // Act
        $categoryPath = $this->tester->getLocator()->category()->service()->getCategoryPath($this->createCategoryPathNodeCollection([]));

        // Assert
        $this->assertSame([], $categoryPath);
    }

    /**
     * @param list<array{0: int, 1: string, 2: list<int>}> $categories Category id, name and parent category ids.
     */
    protected function createCategoryPathNodeCollection(array $categories): CategoryPathNodeCollectionTransfer
    {
        $categoryPathNodeCollectionTransfer = new CategoryPathNodeCollectionTransfer();

        foreach ($categories as [$idCategory, $name, $parentCategoryIds]) {
            $categoryPathNodeCollectionTransfer->addNode(
                (new CategoryPathNodeTransfer())
                    ->setIdCategory($idCategory)
                    ->setName($name)
                    ->setParentCategoryIds($parentCategoryIds),
            );
        }

        return $categoryPathNodeCollectionTransfer;
    }
}
