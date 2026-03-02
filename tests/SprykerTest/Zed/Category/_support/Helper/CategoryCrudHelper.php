<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category\Helper;

use Codeception\Module;
use Codeception\Stub;
use Codeception\Stub\Expected;
use Generated\Shared\Transfer\CategoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\CategoryCollectionRequestTransfer;
use Generated\Shared\Transfer\CategoryCollectionResponseTransfer;
use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\CategoryConditionsTransfer;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Spryker\Zed\Category\Business\CategoryFacadeInterface;
use Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryCreateAfterPluginInterface;
use Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryTransferExpanderPluginInterface;
use Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryUpdateAfterPluginInterface;
use SprykerTest\Zed\Testify\Helper\Business\BusinessHelperTrait;

class CategoryCrudHelper extends Module
{
    use BusinessHelperTrait;
    use CategoryDataHelperTrait;

    /**
     * @var string
     */
    protected const UUID_ONE = 'ebad5042-0db1-498e-9981-42f45f98ad3d';

    /**
     * @var string
     */
    protected const UUID_TWO = 'b7b94e0f-ec4d-4341-9132-07342b45f659';

    public function haveCategoryTransferTwoPersisted(): ?CategoryTransfer
    {
        return $this->persistCategory($this->haveCategoryTransfer(['category_key' => static::UUID_TWO]));
    }

    public function haveCategoryTransferTwo(): CategoryTransfer
    {
        return $this->haveCategoryTransfer(['category_key' => static::UUID_ONE]);
    }

    public function haveCategoryTransferOnePersisted(): ?CategoryTransfer
    {
        return $this->persistCategory($this->haveCategoryTransfer(['category_key' => static::UUID_ONE]));
    }

    public function haveCategoryTransferOne(): CategoryTransfer
    {
        return $this->haveCategoryTransfer(['category_key' => static::UUID_ONE]);
    }

    /**
     * @param array<string, mixed> $seed
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    public function haveCategoryTransfer(array $seed = []): CategoryTransfer
    {
        return $this->getCategoryDataHelper()->haveCategoryTransfer($seed);
    }

    public function haveCategoryCriteriaTransferOneCriteria(): CategoryCriteriaTransfer
    {
        $categoryCriteriaTransfer = new CategoryCriteriaTransfer();
        $categoryConditionsTransfer = new CategoryConditionsTransfer();
        $categoryConditionsTransfer->setCategoryKeys([static::UUID_ONE]);
        $categoryCriteriaTransfer->setCategoryConditions($categoryConditionsTransfer);

        return $categoryCriteriaTransfer;
    }

    public function haveCategoryDeleteCriteriaTransferOneCriteria(): CategoryCollectionDeleteCriteriaTransfer
    {
        $categoryCollectionDeleteCriteriaTransfer = new CategoryCollectionDeleteCriteriaTransfer();
        $categoryCollectionDeleteCriteriaTransfer->setCategoryKeys([static::UUID_ONE]);

        return $categoryCollectionDeleteCriteriaTransfer;
    }

    public function haveCategoryDeleteCriteriaTransferTwoCriteria(): CategoryCollectionDeleteCriteriaTransfer
    {
        $categoryCollectionDeleteCriteriaTransfer = new CategoryCollectionDeleteCriteriaTransfer();
        $categoryCollectionDeleteCriteriaTransfer->setCategoryKeys([static::UUID_TWO]);

        return $categoryCollectionDeleteCriteriaTransfer;
    }

    public function haveCategoryCriteriaTransferTwoCriteria(): CategoryCriteriaTransfer
    {
        $categoryCriteriaTransfer = new CategoryCriteriaTransfer();
        $categoryConditionsTransfer = new CategoryConditionsTransfer();
        $categoryConditionsTransfer->setCategoryKeys([static::UUID_TWO]);
        $categoryCriteriaTransfer->setCategoryConditions($categoryConditionsTransfer);

        return $categoryCriteriaTransfer;
    }

    /**
     * @param array<string, mixed> $seed
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer|null
     */
    public function haveCategoryTransferPersisted(array $seed = []): ?CategoryTransfer
    {
        return $this->persistCategory($this->haveCategoryTransfer($seed));
    }

    public function haveCategoryExpanderPluginSetUuidTwoEnabled(): void
    {
        $categoryExpanderPluginSetUuidTwo = new class (static::UUID_TWO) implements CategoryTransferExpanderPluginInterface {
           /**
            * @var string
            */
            private $categoryKey;

            public function __construct(string $categoryKey)
            {
                $this->categoryKey = $categoryKey;
            }

            public function expandCategory(CategoryTransfer $categoryTransfer): CategoryTransfer
            {
                $categoryTransfer->setCategoryKey($this->categoryKey);

                return $categoryTransfer;
            }
        };

        $this->getBusinessHelper()->mockFactoryMethod('getCategoryTransferExpanderPlugins', [$categoryExpanderPluginSetUuidTwo], 'Category');
    }

    public function haveCategoryPostCreatePluginSetUuidTwoEnabled(): void
    {
        $categoryPostCreatePlugin = $this->mockCreatePlugin();

        $this->getBusinessHelper()->mockFactoryMethod('getCategoryCreateAfterPlugins', [$categoryPostCreatePlugin], 'Category');
    }

    public function haveCategoryPostUpdatePluginSetUuidTwoEnabled(): void
    {
        $categoryPostUpdatePlugin = $this->mockUpdatePlugin();

        $this->getBusinessHelper()->mockFactoryMethod('getCategoryUpdateAfterPlugins', [$categoryPostUpdatePlugin], 'Category');
    }

    public function haveCategoryAlwaysFailingCreateValidatorRuleEnabled(): void
    {
        $this->mockCategoryAlwaysFailingValidatorRule('getCategoryCreateValidatorRules');
    }

    public function haveCategoryAlwaysFailingUpdateValidatorRuleEnabled(): void
    {
        $this->mockCategoryAlwaysFailingValidatorRule('getCategoryUpdateValidatorRules');
    }

    public function assertCategoryCollectionIsEmpty(CategoryCollectionTransfer $categoryCollectionTransfer): void
    {
        $this->assertCount(0, $categoryCollectionTransfer->getCategories(), sprintf('Expected to have an empty collection but found "%s" items', $categoryCollectionTransfer->getCategories()->count()));
    }

    public function assertCategoryCollectionResponseIsEmpty(CategoryCollectionResponseTransfer $categoryCollectionResponseTransfer): void
    {
        $this->assertCount(0, $categoryCollectionResponseTransfer->getCategories(), sprintf('Expected to have an empty response collection but found "%s" items', $categoryCollectionResponseTransfer->getCategories()->count()));
    }

    public function assertCategoryCollectionResponseContainsOneOneTransferWithId(
        CategoryCollectionResponseTransfer $categoryCollectionResponseTransfer,
        CategoryTransfer $categoryTransfer
    ): void {
        $categoryCollectionTransfer = (new CategoryCollectionTransfer())->setCategories($categoryCollectionResponseTransfer->getCategories());

        $this->assertCategoryCollectionContainsTransferWithId($categoryCollectionTransfer, $categoryTransfer);
        $this->assertCategoryCollectionResponseContainsOneOneTransfer($categoryCollectionResponseTransfer);
    }

    public function assertCategoryCollectionResponseContainsOneOneTransfer(CategoryCollectionResponseTransfer $categoryCollectionResponseTransfer): void
    {
        $this->assertCount(1, $categoryCollectionResponseTransfer->getCategories());
        $this->assertEquals(static::UUID_ONE, $categoryCollectionResponseTransfer->getCategories()[0]->getCategoryKey());
    }

    public function assertCategoryCollectionResponseContainsOneTwoTransferWithId(
        CategoryCollectionResponseTransfer $categoryCollectionResponseTransfer,
        CategoryTransfer $categoryTransfer
    ): void {
        $categoryCollectionTransfer = (new CategoryCollectionTransfer())->setCategories($categoryCollectionResponseTransfer->getCategories());

        $this->assertCategoryCollectionContainsTransferWithId($categoryCollectionTransfer, $categoryTransfer);
        $this->assertCategoryCollectionResponseContainsOneTwoTransfer($categoryCollectionResponseTransfer);
    }

    public function assertCategoryCollectionResponseContainsOneTwoTransfer(CategoryCollectionResponseTransfer $categoryCollectionResponseTransfer): void
    {
        $this->assertCount(1, $categoryCollectionResponseTransfer->getCategories());
        $this->assertEquals(static::UUID_TWO, $categoryCollectionResponseTransfer->getCategories()[0]->getCategoryKey());
    }

    public function assertCategoryCollectionContainsTransferWithId(
        CategoryCollectionTransfer $categoryCollectionTransfer,
        CategoryTransfer $categoryTransfer
    ): void {
        $transferFound = false;

        foreach ($categoryCollectionTransfer->getCategories() as $categoryTransferFromCollection) {
            if ($categoryTransferFromCollection->getIdCategory() === $categoryTransfer->getIdCategory()) {
                $transferFound = true;
            }
        }

        $this->assertTrue($transferFound, sprintf('Expected to have a transfer in the collection but transfer by id "%s" was not found in the collection', $categoryTransfer->getIdCategory()));
    }

    public function assertCategoryCollectionResponseContainsFailedValidationRuleError(
        CategoryCollectionResponseTransfer $categoryCollectionResponseTransfer,
        string $message = 'Validation failed'
    ): void {
        $errorFound = false;

        foreach ($categoryCollectionResponseTransfer->getErrors() as $errorTransfer) {
            if ($errorTransfer->getMessage() === $message) {
                $errorFound = true;
            }
        }

        $this->assertTrue($errorFound, sprintf('Expected to have a message "%s" in the error collection but was not found', $message));
    }

    protected function persistCategory(CategoryTransfer $categoryTransfer): ?CategoryTransfer
    {
        $categoryCollectionRequestTransfer = new CategoryCollectionRequestTransfer();
        $categoryCollectionRequestTransfer->addCategory($categoryTransfer);

        $categoryCollectionResponseTransfer = $this->getFacade()->createCategoryCollection($categoryCollectionRequestTransfer);

        return $categoryCollectionResponseTransfer->getCategories()->getIterator()->current();
    }

    protected function mockCreatePlugin(int $expectedInvocations = 1): CategoryCreateAfterPluginInterface
    {
        return Stub::makeEmpty(CategoryCreateAfterPluginInterface::class, [
            'execute' => Expected::exactly(
                $expectedInvocations,
            ),
        ]);
    }

    protected function mockUpdatePlugin(int $expectedInvocations = 1): CategoryUpdateAfterPluginInterface
    {
        return Stub::makeEmpty(CategoryUpdateAfterPluginInterface::class, [
            'execute' => Expected::exactly($expectedInvocations),
        ]);
    }

    protected function mockCategoryAlwaysFailingValidatorRule(string $factoryMethod): void
    {
        $categoryValidatorRule = new class implements \Spryker\Zed\Category\Business\Category\Validator\Rules\CategoryValidatorRuleInterface {
            /**
             * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
             *
             * @return array<string>
             */
            public function validate(CategoryTransfer $categoryTransfer): array
            {
                return ['Validation failed'];
            }
        };

        $this->getBusinessHelper()->mockFactoryMethod($factoryMethod, [$categoryValidatorRule], 'Category');
    }

    protected function getFacade(): CategoryFacadeInterface
    {
        /** @phpstan-var \Spryker\Zed\Category\Business\CategoryFacadeInterface */
        return $this->getBusinessHelper()->getFacade('Category');
    }
}
