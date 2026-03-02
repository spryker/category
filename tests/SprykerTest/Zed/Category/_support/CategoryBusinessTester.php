<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category;

use Codeception\Actor;
use Generated\Shared\DataBuilder\CategoryLocalizedAttributesBuilder;
use Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\LocalizedAttributesTransfer;
use Orm\Zed\Category\Persistence\Map\SpyCategoryStoreTableMap;
use Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery;
use Orm\Zed\Category\Persistence\SpyCategoryStoreQuery;

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 * @method \Spryker\Zed\Category\Business\CategoryFacadeInterface getFacade()
 *
 * @SuppressWarnings(\SprykerTest\Zed\Category\PHPMD)
 */
class CategoryBusinessTester extends Actor
{
    use _generated\CategoryBusinessTesterActions;

    /**
     * @var string
     */
    protected const COL_DEPTH = 'depth';

    /**
     * @var list<string>
     */
    protected const LOCALES = [
        'en_US',
        'de_DE',
    ];

    public function getStoresCountByIdCategory(int $idCategory): int
    {
        return $this->getCategoryStoreTableQuery()
            ->filterByFkCategory($idCategory)
            ->count();
    }

    public function createCategoryLocalizedAttributesForLocale(
        LocaleTransfer $localeTransfer,
        int $idCategory,
        array $seedData = []
    ): CategoryLocalizedAttributesTransfer {
        $categoryLocalizedAttributesData = (new CategoryLocalizedAttributesBuilder($seedData))->build()->toArray();
        $categoryLocalizedAttributesData[LocalizedAttributesTransfer::LOCALE] = $localeTransfer;

        return $this->haveCategoryLocalizedAttributeForCategory($idCategory, $categoryLocalizedAttributesData);
    }

    /**
     * @param array<\Generated\Shared\Transfer\LocaleTransfer> $localeTransfers
     * @param array<int> $storeIds
     * @param int $numberOfChildren
     *
     * @return array<\Generated\Shared\Transfer\CategoryTransfer>
     */
    public function createCategoryWithChildrenAndRelations(
        array $localeTransfers,
        array $storeIds,
        int $numberOfChildren
    ): array {
        $parentCategoryTransfer = $this->createCategoryWithRelations($localeTransfers, $storeIds);

        $categoryTransfers = [
            $parentCategoryTransfer,
        ];

        for ($i = 0; $i < $numberOfChildren; $i++) {
            $categoryTransfers[] = $this->createCategoryWithRelations(
                $localeTransfers,
                $storeIds,
                [
                    CategoryTransfer::PARENT_CATEGORY_NODE => $parentCategoryTransfer->getCategoryNode()->toArray(),
                ],
            );
        }

        return $categoryTransfers;
    }

    public function findCategoryClosureTableDepth(int $idCategoryNode, int $idCategoryNodeDescendant): ?int
    {
        return $this->getCategoryClosureTableQuery()
            ->filterByFkCategoryNode($idCategoryNode)
            ->filterByFkCategoryNodeDescendant($idCategoryNodeDescendant)
            ->select(static::COL_DEPTH)
            ->findOne();
    }

    public function createCategoryTransferWithLocalizedAttributes(): CategoryTransfer
    {
        $categoryTransfer = $this->haveCategory();

        foreach (static::LOCALES as $localeName) {
            $localeTransfer = $this->haveLocale([
                LocaleTransfer::LOCALE_NAME => $localeName,
                LocaleTransfer::IS_ACTIVE => true,
            ]);

            $categoryLocalizedAttributeTransfer = $this->haveCategoryLocalizedAttributeForCategory(
                $categoryTransfer->getIdCategory(),
                [LocalizedAttributesTransfer::LOCALE => $localeTransfer] + (new CategoryLocalizedAttributesBuilder())->build()->toArray(),
            );

            $categoryTransfer->addLocalizedAttributes($categoryLocalizedAttributeTransfer);
        }

        return $categoryTransfer;
    }

    /**
     * @param int $idCategory
     *
     * @return list<int>
     */
    public function getCategoryRelationStoreIds(int $idCategory): array
    {
        return $this->getCategoryStoreTableQuery()
            ->filterByFkCategory($idCategory)
            ->select(SpyCategoryStoreTableMap::COL_FK_STORE)
            ->find()
            ->getData();
    }

    public function createCategoryLocalizedAttributesTransferForLocale(LocaleTransfer $localeTransfer): CategoryLocalizedAttributesTransfer
    {
        return (new CategoryLocalizedAttributesBuilder([
            CategoryLocalizedAttributesTransfer::LOCALE => $localeTransfer->toArray(),
        ]))->build();
    }

    /**
     * @param array<\Generated\Shared\Transfer\LocaleTransfer> $localeTransfers
     * @param array<int> $storeIds
     * @param array $seedData
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    protected function createCategoryWithRelations(
        array $localeTransfers,
        array $storeIds,
        array $seedData = []
    ): CategoryTransfer {
        foreach ($localeTransfers as $localeTransfer) {
            $localizedAttribute = $this->createCategoryLocalizedAttributesTransferForLocale($localeTransfer);

            $seedData[CategoryTransfer::LOCALIZED_ATTRIBUTES][] = $localizedAttribute->toArray();
        }

        $categoryTransfer = $this->haveCategory($seedData);

        foreach ($storeIds as $idStore) {
            $this->haveCategoryStoreRelation($categoryTransfer->getIdCategory(), $idStore);
        }

        return $categoryTransfer;
    }

    protected function getCategoryClosureTableQuery(): SpyCategoryClosureTableQuery
    {
        return SpyCategoryClosureTableQuery::create();
    }

    protected function getCategoryStoreTableQuery(): SpyCategoryStoreQuery
    {
        return SpyCategoryStoreQuery::create();
    }
}
