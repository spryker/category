<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Deleter;

use Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface;

class CategoryStoreDeleter implements CategoryStoreDeleterInterface
{
    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface
     */
    protected $categoryEntityManager;

    public function __construct(CategoryEntityManagerInterface $categoryEntityManager)
    {
        $this->categoryEntityManager = $categoryEntityManager;
    }

    public function deleteCategoryStoreRelations(int $idCategory): void
    {
        $this->categoryEntityManager->deleteCategoryStoreRelations($idCategory);
    }
}
