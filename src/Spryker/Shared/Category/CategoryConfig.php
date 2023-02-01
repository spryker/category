<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Category;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class CategoryConfig extends AbstractSharedConfig
{
    /**
     * Used as `item_type` for touch mechanism.
     *
     * @var string
     */
    public const RESOURCE_TYPE_CATEGORY_NODE = 'categorynode';

    /**
     * Used as `item_type` for touch mechanism.
     *
     * @var string
     */
    public const RESOURCE_TYPE_NAVIGATION = 'navigation';
}
