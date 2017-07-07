<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category;

use Spryker\Shared\Category\CategoryConfig as SharedCategoryConfig;
use Spryker\Shared\Category\CategoryConstants;

class CategoryConfig extends SharedCategoryConfig
{

    /**
     * Default available template for category
     */
    const CATEGORY_TEMPLATE_DEFAULT = 'Category';

    /**
     * @return array
     */
    public function getTemplateList()
    {
        return [
            static::CATEGORY_TEMPLATE_DEFAULT => ''
        ];
    }

}
