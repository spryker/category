<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\Category;

use Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer;
use Spryker\Service\Kernel\AbstractService;

/**
 * @method \Spryker\Service\Category\CategoryServiceFactory getFactory()
 */
class CategoryService extends AbstractService implements CategoryServiceInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryPathNodeCollectionTransfer $categoryPathNodeCollectionTransfer
     *
     * @return list<string>
     */
    public function getCategoryPath(CategoryPathNodeCollectionTransfer $categoryPathNodeCollectionTransfer): array
    {
        return $this->getFactory()
            ->createCategoryPathResolver()
            ->resolveCategoryPath($categoryPathNodeCollectionTransfer);
    }
}
