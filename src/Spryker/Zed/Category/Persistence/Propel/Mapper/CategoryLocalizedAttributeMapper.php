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

class CategoryLocalizedAttributeMapper
{
    public function mapCategoryLocalizedAttributeTransferToCategoryAttributeEntity(
        CategoryLocalizedAttributesTransfer $categoryLocalizedAttributesTransfer,
        SpyCategoryAttribute $categoryAttributeEntity
    ): SpyCategoryAttribute {
        $categoryAttributeEntity->fromArray($categoryLocalizedAttributesTransfer->modifiedToArray());
        $categoryAttributeEntity->setFkLocale($categoryLocalizedAttributesTransfer->getLocaleOrFail()->getIdLocaleOrFail());

        return $categoryAttributeEntity;
    }

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\Category\Persistence\SpyCategoryAttribute> $categoryAttributeEntities
     *
     * @return array<int, array<\Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer>>
     */
    public function mapCategoryAttributeEntitiesToCategoryLocalizedAttributesTransfersGroupedByIdCategory(
        Collection $categoryAttributeEntities
    ): array {
        $categoryLocalizedAttributesTransfers = [];
        foreach ($categoryAttributeEntities as $categoryAttributeEntity) {
            $categoryLocalizedAttributesTransfers[$categoryAttributeEntity->getFkCategory()][] = $this->mapCategoryAttributeEntityToCategoryLocalizedAttributesTransfer(
                $categoryAttributeEntity,
                new CategoryLocalizedAttributesTransfer(),
            );
        }

        return $categoryLocalizedAttributesTransfers;
    }

    protected function mapCategoryAttributeEntityToCategoryLocalizedAttributesTransfer(
        SpyCategoryAttribute $categoryAttributeEntity,
        CategoryLocalizedAttributesTransfer $categoryLocalizedAttributesTransfer
    ): CategoryLocalizedAttributesTransfer {
        $localeTransfer = new LocaleTransfer();
        $localeTransfer->fromArray($categoryAttributeEntity->getLocale()->toArray(), true);

        $categoryLocalizedAttributesTransfer->fromArray($categoryAttributeEntity->toArray(), true);
        $categoryLocalizedAttributesTransfer->setLocale($localeTransfer);

        return $categoryLocalizedAttributesTransfer;
    }
}
