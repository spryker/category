<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category;

use Spryker\Zed\CategoryGui\Communication\Controller\ListController;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class CategoryConfig extends AbstractBundleConfig
{

    /**
     * Used as `item_type` for touch mechanism.
     */
    const RESOURCE_TYPE_CATEGORY_NODE = 'categorynode';

    /**
     * Used as `item_type` for touch mechanism.
     */
    const RESOURCE_TYPE_NAVIGATION = 'navigation';

    protected const REDIRECT_URL_DEFAULT = '/category/root';

    protected const REDIRECT_URL_CATEGORY_GUI = '/category-gui/list';

    /**
     * @return string
     */
    public function getDefaultRedirectUrl(): string
    {
        if (class_exists(ListController::class)) {
            return static::REDIRECT_URL_CATEGORY_GUI;
        }

        return static::REDIRECT_URL_DEFAULT;
    }
}
