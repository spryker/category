<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Deleter;

use Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface;

class CategoryClosureTableDeleter implements CategoryClosureTableDeleterInterface
{
    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface
     */
    protected $categoryEntityManager;

    public function __construct(CategoryEntityManagerInterface $categoryEntityManager)
    {
        $this->categoryEntityManager = $categoryEntityManager;
    }

    public function deleteCategoryClosureTable(int $idCategoryNode): void
    {
        $this->categoryEntityManager->deleteCategoryClosureTable($idCategoryNode);
    }
}
