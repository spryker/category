<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category\Business\Product\Validator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionResponseTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Spryker\Zed\Category\Business\Product\Validator\ProductCategoryValidator;
use Spryker\Zed\Category\Persistence\CategoryRepositoryInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Category
 * @group Business
 * @group Product
 * @group Validator
 * @group ProductCategoryValidatorTest
 *
 * Add your own group annotations below this line
 */
class ProductCategoryValidatorTest extends Unit
{
    protected const string KNOWN_UUID = '11111111-1111-1111-1111-111111111111';

    protected const string OTHER_KNOWN_UUID = '22222222-2222-2222-2222-222222222222';

    protected const string UNKNOWN_UUID = '00000000-0000-0000-0000-000000000000';

    protected const string PRODUCT_ABSTRACT_SKU = 'abstract-sku';

    protected const string OTHER_PRODUCT_ABSTRACT_SKU = 'other-abstract-sku';

    protected const string NUMERIC_PRODUCT_ABSTRACT_SKU = '123';

    protected const string ERROR_MESSAGE_PATTERN = 'Category with UUID "%s" does not exist.';

    public function testValidateSkipsValidationWhenCategoryUuidIsNotSupported(): void
    {
        // Arrange
        $categoryRepositoryMock = $this->createCategoryRepositoryMock(false);
        $categoryRepositoryMock->expects($this->never())->method('getCategoryCollection');

        $productAbstractCollectionRequestTransfer = $this->createRequest([
            static::PRODUCT_ABSTRACT_SKU => [static::UNKNOWN_UUID],
        ]);

        // Act
        $productAbstractCollectionResponseTransfer = (new ProductCategoryValidator($categoryRepositoryMock))
            ->validateProductAbstractCollection(
                $productAbstractCollectionRequestTransfer,
                new ProductAbstractCollectionResponseTransfer(),
            );

        // Assert
        $this->assertCount(0, $productAbstractCollectionResponseTransfer->getErrors());
    }

    public function testValidateReturnsNoErrorsWhenAllCategoryUuidsAreKnown(): void
    {
        // Arrange
        $categoryRepositoryMock = $this->createCategoryRepositoryMock(true, [static::KNOWN_UUID, static::OTHER_KNOWN_UUID]);

        $productAbstractCollectionRequestTransfer = $this->createRequest([
            static::PRODUCT_ABSTRACT_SKU => [static::KNOWN_UUID, static::OTHER_KNOWN_UUID],
        ]);

        // Act
        $productAbstractCollectionResponseTransfer = (new ProductCategoryValidator($categoryRepositoryMock))
            ->validateProductAbstractCollection(
                $productAbstractCollectionRequestTransfer,
                new ProductAbstractCollectionResponseTransfer(),
            );

        // Assert
        $this->assertCount(0, $productAbstractCollectionResponseTransfer->getErrors());
    }

    public function testValidateReturnsErrorWithMessageForUnknownCategoryUuid(): void
    {
        // Arrange
        $categoryRepositoryMock = $this->createCategoryRepositoryMock(true);

        $productAbstractCollectionRequestTransfer = $this->createRequest([
            static::PRODUCT_ABSTRACT_SKU => [static::UNKNOWN_UUID],
        ]);

        // Act
        $productAbstractCollectionResponseTransfer = (new ProductCategoryValidator($categoryRepositoryMock))
            ->validateProductAbstractCollection(
                $productAbstractCollectionRequestTransfer,
                new ProductAbstractCollectionResponseTransfer(),
            );

        // Assert
        $this->assertCount(1, $productAbstractCollectionResponseTransfer->getErrors());
        $errorTransfer = $productAbstractCollectionResponseTransfer->getErrors()->offsetGet(0);
        $this->assertSame(static::PRODUCT_ABSTRACT_SKU, $errorTransfer->getEntityIdentifier());
        $this->assertSame(sprintf(static::ERROR_MESSAGE_PATTERN, static::UNKNOWN_UUID), $errorTransfer->getMessage());
    }

    public function testValidateReturnsErrorsOnlyForUnknownCategoryUuids(): void
    {
        // Arrange
        $categoryRepositoryMock = $this->createCategoryRepositoryMock(true, [static::KNOWN_UUID]);

        $productAbstractCollectionRequestTransfer = $this->createRequest([
            static::PRODUCT_ABSTRACT_SKU => [static::KNOWN_UUID, static::UNKNOWN_UUID],
        ]);

        // Act
        $productAbstractCollectionResponseTransfer = (new ProductCategoryValidator($categoryRepositoryMock))
            ->validateProductAbstractCollection(
                $productAbstractCollectionRequestTransfer,
                new ProductAbstractCollectionResponseTransfer(),
            );

        // Assert
        $this->assertCount(1, $productAbstractCollectionResponseTransfer->getErrors());
        $this->assertSame(
            sprintf(static::ERROR_MESSAGE_PATTERN, static::UNKNOWN_UUID),
            $productAbstractCollectionResponseTransfer->getErrors()->offsetGet(0)->getMessage(),
        );
    }

    public function testValidateReturnsSingleErrorForDuplicatedCategoryUuid(): void
    {
        // Arrange
        $categoryRepositoryMock = $this->createCategoryRepositoryMock(true);

        $productAbstractCollectionRequestTransfer = $this->createRequest([
            static::PRODUCT_ABSTRACT_SKU => [static::UNKNOWN_UUID, static::UNKNOWN_UUID],
        ]);

        // Act
        $productAbstractCollectionResponseTransfer = (new ProductCategoryValidator($categoryRepositoryMock))
            ->validateProductAbstractCollection(
                $productAbstractCollectionRequestTransfer,
                new ProductAbstractCollectionResponseTransfer(),
            );

        // Assert
        $this->assertCount(1, $productAbstractCollectionResponseTransfer->getErrors());
    }

    public function testValidateReturnsErrorPerProductAbstractForSameUnknownCategoryUuid(): void
    {
        // Arrange
        $categoryRepositoryMock = $this->createCategoryRepositoryMock(true);

        $productAbstractCollectionRequestTransfer = $this->createRequest([
            static::PRODUCT_ABSTRACT_SKU => [static::UNKNOWN_UUID],
            static::OTHER_PRODUCT_ABSTRACT_SKU => [static::UNKNOWN_UUID],
        ]);

        // Act
        $productAbstractCollectionResponseTransfer = (new ProductCategoryValidator($categoryRepositoryMock))
            ->validateProductAbstractCollection(
                $productAbstractCollectionRequestTransfer,
                new ProductAbstractCollectionResponseTransfer(),
            );

        // Assert
        $this->assertCount(2, $productAbstractCollectionResponseTransfer->getErrors());
        $entityIdentifiers = [];

        foreach ($productAbstractCollectionResponseTransfer->getErrors() as $errorTransfer) {
            $entityIdentifiers[] = $errorTransfer->getEntityIdentifier();
        }

        $this->assertEqualsCanonicalizing(
            [static::PRODUCT_ABSTRACT_SKU, static::OTHER_PRODUCT_ABSTRACT_SKU],
            $entityIdentifiers,
        );
    }

    public function testValidateIgnoresEmptyCategoryUuids(): void
    {
        // Arrange
        $categoryRepositoryMock = $this->createCategoryRepositoryMock(true);
        $categoryRepositoryMock->expects($this->never())->method('getCategoryCollection');

        $productAbstractCollectionRequestTransfer = $this->createRequest([
            static::PRODUCT_ABSTRACT_SKU => [''],
        ]);

        // Act
        $productAbstractCollectionResponseTransfer = (new ProductCategoryValidator($categoryRepositoryMock))
            ->validateProductAbstractCollection(
                $productAbstractCollectionRequestTransfer,
                new ProductAbstractCollectionResponseTransfer(),
            );

        // Assert
        $this->assertCount(0, $productAbstractCollectionResponseTransfer->getErrors());
    }

    public function testValidateReturnsNumericSkuAsStringEntityIdentifier(): void
    {
        // Arrange
        $categoryRepositoryMock = $this->createCategoryRepositoryMock(true);

        $productAbstractCollectionRequestTransfer = $this->createRequest([
            static::NUMERIC_PRODUCT_ABSTRACT_SKU => [static::UNKNOWN_UUID],
        ]);

        // Act
        $productAbstractCollectionResponseTransfer = (new ProductCategoryValidator($categoryRepositoryMock))
            ->validateProductAbstractCollection(
                $productAbstractCollectionRequestTransfer,
                new ProductAbstractCollectionResponseTransfer(),
            );

        // Assert
        $this->assertCount(1, $productAbstractCollectionResponseTransfer->getErrors());
        $this->assertSame(
            static::NUMERIC_PRODUCT_ABSTRACT_SKU,
            $productAbstractCollectionResponseTransfer->getErrors()->offsetGet(0)->getEntityIdentifier(),
        );
    }

    /**
     * @param array<string> $knownUuids
     *
     * @return \Spryker\Zed\Category\Persistence\CategoryRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createCategoryRepositoryMock(bool $isCategoryUuidSupported, array $knownUuids = []): CategoryRepositoryInterface
    {
        $categoryCollectionTransfer = new CategoryCollectionTransfer();

        foreach ($knownUuids as $knownUuid) {
            $categoryCollectionTransfer->addCategory((new CategoryTransfer())->setUuid($knownUuid));
        }

        $categoryRepositoryMock = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepositoryMock->method('isCategoryUuidSupported')->willReturn($isCategoryUuidSupported);
        $categoryRepositoryMock->method('getCategoryCollection')->willReturn($categoryCollectionTransfer);

        return $categoryRepositoryMock;
    }

    /**
     * @param array<string, array<string>> $categoryUuidsBySku
     *
     * @return \Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer
     */
    protected function createRequest(array $categoryUuidsBySku): ProductAbstractCollectionRequestTransfer
    {
        $productAbstractCollectionRequestTransfer = new ProductAbstractCollectionRequestTransfer();

        foreach ($categoryUuidsBySku as $sku => $categoryUuids) {
            $productAbstractCollectionRequestTransfer->addProductAbstract(
                (new ProductAbstractTransfer())
                    ->setSku((string)$sku)
                    ->setCategoryUuids($categoryUuids),
            );
        }

        return $productAbstractCollectionRequestTransfer;
    }
}
