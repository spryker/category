<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Dependency\Plugin;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryUrlPathPluginInterface as ExtensionCategoryUrlPathPluginInterface;

/**
 * @deprecated Use {@link \Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryUrlPathPluginInterface} instead.
 */
interface CategoryUrlPathPluginInterface extends ExtensionCategoryUrlPathPluginInterface
{
    /**
     * Specification:
     * - Update category url paths returned array.
     *
     * @api
     *
     * @param array $paths
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return array
     */
    public function update(array $paths, LocaleTransfer $localeTransfer);
}
