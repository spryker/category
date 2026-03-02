<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Updater;

use Generated\Shared\Transfer\CategoryTransfer;
use Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class CategoryAttributeUpdater implements CategoryAttributeUpdaterInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface
     */
    protected $categoryEntityManager;

    public function __construct(CategoryEntityManagerInterface $categoryEntityManager)
    {
        $this->categoryEntityManager = $categoryEntityManager;
    }

    public function updateCategoryAttributes(CategoryTransfer $categoryTransfer): void
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($categoryTransfer) {
            $this->executeUpdateCategoryAttributesTransaction($categoryTransfer);
        });
    }

    protected function executeUpdateCategoryAttributesTransaction(CategoryTransfer $categoryTransfer): void
    {
        $idCategory = $categoryTransfer->getIdCategoryOrFail();

        foreach ($categoryTransfer->getLocalizedAttributes() as $categoryLocalizedAttributesTransfer) {
            $this->categoryEntityManager->saveCategoryAttribute($idCategory, $categoryLocalizedAttributesTransfer);
        }
    }
}
