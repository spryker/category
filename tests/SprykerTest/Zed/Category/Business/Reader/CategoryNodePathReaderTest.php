<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category\Business\Reader;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Category\Business\Reader\CategoryNodePathReader;
use Spryker\Zed\Category\CategoryConfig;
use Spryker\Zed\Category\Persistence\CategoryRepositoryInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Category
 * @group Business
 * @group Reader
 * @group CategoryNodePathReaderTest
 * Add your own group annotations below this line
 */
class CategoryNodePathReaderTest extends Unit
{
    protected const int BATCH_READ_CHUNK_SIZE = 2;

    protected const array NODE_PATHS_INDEXED_BY_ID_CATEGORY_NODE = [
        11 => 'Demoshop',
        12 => 'Demoshop/Cameras',
        13 => 'Demoshop/Cameras/Camcorders',
    ];

    /**
     * @var \SprykerTest\Zed\Category\CategoryBusinessTester
     */
    protected $tester;

    public function testGetNodePathsIndexedByIdCategoryNodeReadsNodePathsInBatchReadChunksAndKeepsNodeIdKeys(): void
    {
        // Arrange
        $requestedCategoryNodeIdChunks = [];
        $categoryRepositoryMock = $this->createMock(CategoryRepositoryInterface::class);

        // Expect
        $categoryRepositoryMock->expects($this->exactly(2))
            ->method('getNodePathsIndexedByIdCategoryNode')
            ->willReturnCallback(function (array $categoryNodeIds) use (&$requestedCategoryNodeIdChunks): array {
                $requestedCategoryNodeIdChunks[] = $categoryNodeIds;

                return array_intersect_key(static::NODE_PATHS_INDEXED_BY_ID_CATEGORY_NODE, array_flip($categoryNodeIds));
            });

        // Act
        $nodePathsIndexedByIdCategoryNode = $this->createCategoryNodePathReader($categoryRepositoryMock)
            ->getNodePathsIndexedByIdCategoryNode(array_keys(static::NODE_PATHS_INDEXED_BY_ID_CATEGORY_NODE), new LocaleTransfer());

        // Assert
        $this->assertSame([[11, 12], [13]], $requestedCategoryNodeIdChunks);
        $this->assertSame(static::NODE_PATHS_INDEXED_BY_ID_CATEGORY_NODE, $nodePathsIndexedByIdCategoryNode);
    }

    public function testGetNodePathsIndexedByIdCategoryNodeSkipsRepositoryWhenNoNodeIdsAreGiven(): void
    {
        // Arrange
        $categoryRepositoryMock = $this->createMock(CategoryRepositoryInterface::class);

        // Expect
        $categoryRepositoryMock->expects($this->never())->method('getNodePathsIndexedByIdCategoryNode');

        // Act
        $nodePathsIndexedByIdCategoryNode = $this->createCategoryNodePathReader($categoryRepositoryMock)
            ->getNodePathsIndexedByIdCategoryNode([], new LocaleTransfer());

        // Assert
        $this->assertSame([], $nodePathsIndexedByIdCategoryNode);
    }

    protected function createCategoryNodePathReader(CategoryRepositoryInterface $categoryRepository): CategoryNodePathReader
    {
        $categoryConfigMock = $this->createMock(CategoryConfig::class);
        $categoryConfigMock->method('getBatchReadChunkSize')->willReturn(static::BATCH_READ_CHUNK_SIZE);

        return new CategoryNodePathReader($categoryRepository, $categoryConfigMock);
    }
}
