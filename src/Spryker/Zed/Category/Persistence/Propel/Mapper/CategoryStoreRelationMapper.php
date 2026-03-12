<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\StoreConditionsTransfer;
use Generated\Shared\Transfer\StoreCriteriaTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Orm\Zed\Store\Persistence\SpyStore;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\Store\Business\StoreFacadeInterface;

class CategoryStoreRelationMapper
{
    protected const string COL_FK_CATEGORY = 'fk_category';

    protected const string COL_FK_STORE = 'fk_store';

    /**
     * @var array<int|string, \Generated\Shared\Transfer\StoreTransfer>
     */
    protected static array $storeCache = [];

    public function __construct(protected StoreFacadeInterface $storeFacade)
    {
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Category\Persistence\SpyCategoryStore> $categoryStoreEntities
     * @param \Generated\Shared\Transfer\StoreRelationTransfer $storeRelationTransfer
     *
     * @return \Generated\Shared\Transfer\StoreRelationTransfer
     */
    public function mapCategoryStoreEntitiesToStoreRelationTransfer(
        ObjectCollection $categoryStoreEntities,
        StoreRelationTransfer $storeRelationTransfer
    ): StoreRelationTransfer {
        foreach ($categoryStoreEntities as $categoryStoreEntity) {
            $storeTransfer = $this->mapStoreEntityToStoreTransfer($categoryStoreEntity->getSpyStore(), new StoreTransfer());
            $storeRelationTransfer->addStores($storeTransfer);
            $storeRelationTransfer->addIdStores($storeTransfer->getIdStoreOrFail());
        }

        return $storeRelationTransfer;
    }

    /**
     * @param array<array<string, mixed>> $categoryStoreArrays
     * @param \Generated\Shared\Transfer\StoreRelationTransfer $storeRelationTransfer
     *
     * @return \Generated\Shared\Transfer\StoreRelationTransfer
     */
    public function mapCategoryStoreArrayToStoreRelationTransfer(
        array $categoryStoreArrays,
        StoreRelationTransfer $storeRelationTransfer
    ): StoreRelationTransfer {
        foreach ($categoryStoreArrays as $categoryStore) {
            $storeTransfer = $this->getStoreCache($categoryStore);
            $storeRelationTransfer->addStores($storeTransfer);
            $storeRelationTransfer->addIdStores($storeTransfer->getIdStoreOrFail());
        }

        return $storeRelationTransfer;
    }

    /**
     * @param \Propel\Runtime\Collection\Collection $categoryStoreArray
     *
     * @return array<\Generated\Shared\Transfer\StoreRelationTransfer>
     */
    public function mapCategoryStoreEntitiesToStoreRelationTransfers(Collection $categoryStoreArray): array
    {
        $storeRelationTransfers = [];

        $groupedCategoryStoreEntities = $this->getCategoryStoreEntitiesGroupedByIdCategory($categoryStoreArray);
        foreach ($groupedCategoryStoreEntities as $idCategory => $categoryStoreArrayByCategoryId) {
            $storeRelationTransfers[] = $this->mapCategoryStoreArrayToStoreRelationTransfer(
                $categoryStoreArrayByCategoryId,
                (new StoreRelationTransfer())->setIdEntity($idCategory),
            );
        }

        return $storeRelationTransfers;
    }

    protected function mapStoreEntityToStoreTransfer(SpyStore $storeEntity, StoreTransfer $storeTransfer): StoreTransfer
    {
        return $storeTransfer->fromArray($storeEntity->toArray(), true);
    }

    /**
     * @param \Propel\Runtime\Collection\Collection $categoryStoreEntities
     *
     * @return array<int, array<mixed>>
     */
    protected function getCategoryStoreEntitiesGroupedByIdCategory(Collection $categoryStoreEntities): array
    {
        $groupedCategoryStoreEntities = [];
        foreach ($categoryStoreEntities as $categoryStoreArray) {
            $groupedCategoryStoreEntities[$categoryStoreArray[static::COL_FK_CATEGORY]][] = $categoryStoreArray;
        }

        return $groupedCategoryStoreEntities;
    }

    protected function getStoreCache(array $categoryStoreArray): StoreTransfer
    {
        if (!isset(static::$storeCache[$categoryStoreArray[static::COL_FK_STORE]])) {
            $storeCriteriaTransfer = (new StoreCriteriaTransfer())->setStoreConditions(
                (new StoreConditionsTransfer())->setWithExpanders(false),
            );
            $storeCollection = $this->storeFacade->getStoreCollection($storeCriteriaTransfer);
            foreach ($storeCollection->getStores() as $storeTransfer) {
                static::$storeCache[$storeTransfer->getIdStore()] = $storeTransfer;
            }
        }

        return static::$storeCache[$categoryStoreArray[static::COL_FK_STORE]];
    }
}
