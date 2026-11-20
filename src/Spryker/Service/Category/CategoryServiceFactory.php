<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\Category;

use Spryker\Service\Category\Resolver\CategoryPathResolver;
use Spryker\Service\Category\Resolver\CategoryPathResolverInterface;
use Spryker\Service\Kernel\AbstractServiceFactory;

class CategoryServiceFactory extends AbstractServiceFactory
{
    public function createCategoryPathResolver(): CategoryPathResolverInterface
    {
        return new CategoryPathResolver();
    }
}
