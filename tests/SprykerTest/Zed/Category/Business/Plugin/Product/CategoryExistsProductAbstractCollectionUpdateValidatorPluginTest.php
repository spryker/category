<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category\Business\Plugin\Product;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionResponseTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Orm\Zed\Category\Persistence\SpyCategoryQuery;
use Spryker\Zed\Category\Communication\Plugin\Product\CategoryExistsProductAbstractCollectionUpdateValidatorPlugin;
use SprykerTest\Zed\Category\CategoryBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Category
 * @group Business
 * @group Plugin
 * @group Product
 * @group CategoryExistsProductAbstractCollectionUpdateValidatorPluginTest
 *
 * Add your own group annotations below this line
 */
class CategoryExistsProductAbstractCollectionUpdateValidatorPluginTest extends Unit
{
    protected const string UNKNOWN_UUID = '00000000-0000-0000-0000-000000000000';

    protected const string PRODUCT_ABSTRACT_SKU = 'abstract-sku';

    protected CategoryBusinessTester $tester;

    public function testValidateReturnsErrorWhenCategoryUuidDoesNotExist(): void
    {
        // Arrange
        $productAbstractCollectionRequestTransfer = $this->createRequestWithCategoryUuid(static::UNKNOWN_UUID);

        // Act
        $productAbstractCollectionResponseTransfer = (new CategoryExistsProductAbstractCollectionUpdateValidatorPlugin())->validate(
            $productAbstractCollectionRequestTransfer,
            new ProductAbstractCollectionResponseTransfer(),
        );

        // Assert
        $this->assertCount(1, $productAbstractCollectionResponseTransfer->getErrors());
        $this->assertSame(static::PRODUCT_ABSTRACT_SKU, $productAbstractCollectionResponseTransfer->getErrors()->offsetGet(0)->getEntityIdentifier());
    }

    public function testValidateReturnsNoErrorWhenCategoryUuidExists(): void
    {
        // Arrange
        $categoryTransfer = $this->tester->haveLocalizedCategory();
        $categoryUuid = SpyCategoryQuery::create()->findOneByIdCategory($categoryTransfer->getIdCategory())->getUuid();
        $productAbstractCollectionRequestTransfer = $this->createRequestWithCategoryUuid($categoryUuid);

        // Act
        $productAbstractCollectionResponseTransfer = (new CategoryExistsProductAbstractCollectionUpdateValidatorPlugin())->validate(
            $productAbstractCollectionRequestTransfer,
            new ProductAbstractCollectionResponseTransfer(),
        );

        // Assert
        $this->assertCount(0, $productAbstractCollectionResponseTransfer->getErrors());
    }

    protected function createRequestWithCategoryUuid(string $uuid): ProductAbstractCollectionRequestTransfer
    {
        return (new ProductAbstractCollectionRequestTransfer())
            ->addProductAbstract(
                (new ProductAbstractTransfer())
                    ->setSku(static::PRODUCT_ABSTRACT_SKU)
                    ->addCategoryUuid($uuid),
            );
    }
}
