<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Category\Business\Product\Validator;

use Generated\Shared\Transfer\CategoryConditionsTransfer;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionRequestTransfer;
use Generated\Shared\Transfer\ProductAbstractCollectionResponseTransfer;
use Spryker\Zed\Category\Persistence\CategoryRepositoryInterface;

class ProductCategoryValidator implements ProductCategoryValidatorInterface
{
    public function __construct(
        protected readonly CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function validateProductAbstractCollection(
        ProductAbstractCollectionRequestTransfer $productAbstractCollectionRequestTransfer,
        ProductAbstractCollectionResponseTransfer $productAbstractCollectionResponseTransfer
    ): ProductAbstractCollectionResponseTransfer {
        if (!$this->categoryRepository->isCategoryUuidSupported()) {
            return $productAbstractCollectionResponseTransfer;
        }

        $uuidsByEntity = [];

        foreach ($productAbstractCollectionRequestTransfer->getProductAbstracts() as $productAbstractTransfer) {
            $sku = (string)$productAbstractTransfer->getSku();
            foreach ($productAbstractTransfer->getCategoryUuids() as $uuid) {
                if ($uuid !== '') {
                    $uuidsByEntity[$sku][$uuid] = true;
                }
            }
        }

        if ($uuidsByEntity === []) {
            return $productAbstractCollectionResponseTransfer;
        }

        $knownUuids = $this->getKnownUuids($this->flattenUuids($uuidsByEntity));

        foreach ($uuidsByEntity as $sku => $uuids) {
            foreach (array_diff(array_keys($uuids), $knownUuids) as $unknownUuid) {
                $productAbstractCollectionResponseTransfer->addError($this->createError((string)$sku, (string)$unknownUuid));
            }
        }

        return $productAbstractCollectionResponseTransfer;
    }

    /**
     * @param array<string, array<string, true>> $uuidsByEntity
     *
     * @return list<string>
     */
    protected function flattenUuids(array $uuidsByEntity): array
    {
        $uuids = [];

        foreach ($uuidsByEntity as $entityUuids) {
            $uuids = array_merge($uuids, array_keys($entityUuids));
        }

        return array_values(array_unique($uuids));
    }

    /**
     * @param list<string> $uuids
     *
     * @return list<string>
     */
    protected function getKnownUuids(array $uuids): array
    {
        $categoryCriteriaTransfer = (new CategoryCriteriaTransfer())
            ->setCategoryConditions(
                (new CategoryConditionsTransfer())->setUuids($uuids),
            );

        $knownUuids = [];

        foreach ($this->categoryRepository->getCategoryCollection($categoryCriteriaTransfer)->getCategories() as $categoryTransfer) {
            $uuid = $categoryTransfer->getUuid();

            if ($uuid !== null) {
                $knownUuids[] = $uuid;
            }
        }

        return $knownUuids;
    }

    protected function createError(?string $entityIdentifier, string $unknownUuid): ErrorTransfer
    {
        return (new ErrorTransfer())
            ->setEntityIdentifier($entityIdentifier)
            ->setMessage(sprintf('Category with UUID "%s" does not exist.', $unknownUuid));
    }
}
