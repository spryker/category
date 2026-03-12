<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Orm\Zed\Category\Persistence\SpyCategoryAttribute;
use Propel\Runtime\Collection\Collection;
use Spryker\Zed\Locale\Business\LocaleFacadeInterface;

class CategoryLocalizedAttributeMapper
{
    protected const string COL_FK_CATEGORY = 'fk_category';

    protected const string COL_FK_LOCALE = 'fk_locale';

    /**
     * @var array<int, \Generated\Shared\Transfer\LocaleTransfer>
     */
    protected static array $localeCache = [];

    public function __construct(protected LocaleFacadeInterface $localeFacade)
    {
    }

    public function mapCategoryLocalizedAttributeTransferToCategoryAttributeEntity(
        CategoryLocalizedAttributesTransfer $categoryLocalizedAttributesTransfer,
        SpyCategoryAttribute $categoryAttributeEntity
    ): SpyCategoryAttribute {
        $categoryAttributeEntity->fromArray($categoryLocalizedAttributesTransfer->modifiedToArray());
        $categoryAttributeEntity->setFkLocale($categoryLocalizedAttributesTransfer->getLocaleOrFail()->getIdLocaleOrFail());

        return $categoryAttributeEntity;
    }

    /**
     * @param \Propel\Runtime\Collection\Collection $categoryAttributeCollection
     *
     * @return array<int, array<\Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer>>
     */
    public function mapCategoryAttributeEntitiesToCategoryLocalizedAttributesTransfersGroupedByIdCategory(
        Collection $categoryAttributeCollection
    ): array {
        $categoryLocalizedAttributesTransfers = [];
        foreach ($categoryAttributeCollection as $categoryAttributeArray) {
            $categoryLocalizedAttributesTransfers[$categoryAttributeArray[static::COL_FK_CATEGORY]][] = $this->mapCategoryAttributeArrayToCategoryLocalizedAttributesTransfer(
                $categoryAttributeArray,
                new CategoryLocalizedAttributesTransfer(),
            );
        }

        return $categoryLocalizedAttributesTransfers;
    }

    /**
     * @param array<mixed> $categoryAttributeArray
     * @param \Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer $categoryLocalizedAttributesTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer
     */
    protected function mapCategoryAttributeArrayToCategoryLocalizedAttributesTransfer(
        array $categoryAttributeArray,
        CategoryLocalizedAttributesTransfer $categoryLocalizedAttributesTransfer
    ): CategoryLocalizedAttributesTransfer {
        $categoryLocalizedAttributesTransfer->fromArray($categoryAttributeArray, true);
        $categoryLocalizedAttributesTransfer->setLocale($this->getCachedLocale($categoryAttributeArray[static::COL_FK_LOCALE]));

        return $categoryLocalizedAttributesTransfer;
    }

    protected function getCachedLocale(int $localeId): LocaleTransfer
    {
        if (!isset(static::$localeCache[$localeId])) {
            $localeTransferCollection = $this->localeFacade->getLocaleCollection();
            foreach ($localeTransferCollection as $localeTransfer) {
                static::$localeCache[$localeTransfer->getIdLocaleOrFail()] = $localeTransfer;
            }
        }

        return static::$localeCache[$localeId];
    }
}
