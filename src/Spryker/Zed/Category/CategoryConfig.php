<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category;

use Spryker\Shared\Category\CategoryConfig as SharedCategoryConfig;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class CategoryConfig extends AbstractBundleConfig
{
    /**
     * Default available template for category
     */
    public const CATEGORY_TEMPLATE_DEFAULT = 'Catalog (default)';

    /**
     * Used as `item_type` for touch mechanism.
     */
    public const RESOURCE_TYPE_CATEGORY_NODE = SharedCategoryConfig::RESOURCE_TYPE_CATEGORY_NODE;

    /**
     * Used as `item_type` for touch mechanism.
     */
    public const RESOURCE_TYPE_NAVIGATION = SharedCategoryConfig::RESOURCE_TYPE_NAVIGATION;

    protected const REDIRECT_URL_DEFAULT = '/category/root';

    public const BASE_URL_YVES = 'PRODUCT_MANAGEMENT:BASE_URL_YVES';

    /**
     * @return array
     */
    public function getTemplateList()
    {
        return [
            static::CATEGORY_TEMPLATE_DEFAULT => '',
        ];
    }

    /**
     * @return string
     */
    public function getDefaultRedirectUrl(): string
    {
        return static::REDIRECT_URL_DEFAULT;
    }

    /**
     * @return string
     */
    public function getImageUrlPrefix()
    {
        return $this->get(static::BASE_URL_YVES);
    }
}
