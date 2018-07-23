<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Model\CategoryNode;

use Generated\Shared\Transfer\CategoryTransfer;
use Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface;

class CategoryNodeChecker implements CategoryNodeCheckerInterface
{
    /**
     * @var \Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface
     */
    protected $queryContainer;

    /**
     * @param \Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface $queryContainer
     */
    public function __construct(
        CategoryQueryContainerInterface $queryContainer
    ) {
        $this->queryContainer = $queryContainer;
    }

    /**
     * @param string $name
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return bool
     */
    public function hasFirstLevelChildrenByName(string $name, CategoryTransfer $categoryTransfer): bool
    {
        $exists = $this->queryContainer
            ->queryFirstLevelChildrenByName(
                $categoryTransfer->getParentCategoryNode()->getIdCategoryNode(),
                $name
            )
            ->exists();

        if ($exists) {
            return true;
        }

        return false;
    }
}
