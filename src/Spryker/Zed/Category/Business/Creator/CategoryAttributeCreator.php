<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Creator;

use Generated\Shared\Transfer\CategoryTransfer;
use Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class CategoryAttributeCreator implements CategoryAttributeCreatorInterface
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

    public function createCategoryLocalizedAttributes(CategoryTransfer $categoryTransfer): void
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($categoryTransfer) {
            $this->executeCreateCategoryLocalizedAttributesTransaction($categoryTransfer);
        });
    }

    protected function executeCreateCategoryLocalizedAttributesTransaction(CategoryTransfer $categoryTransfer): void
    {
        foreach ($categoryTransfer->getLocalizedAttributes() as $localizedAttributesTransfer) {
            $this->categoryEntityManager->saveCategoryAttribute(
                $categoryTransfer->getIdCategoryOrFail(),
                $localizedAttributesTransfer,
            );
        }
    }
}
