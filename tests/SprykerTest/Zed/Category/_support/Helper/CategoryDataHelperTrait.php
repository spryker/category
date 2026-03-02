<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category\Helper;

use Codeception\Module;

trait CategoryDataHelperTrait
{
    protected function getCategoryDataHelper(): CategoryDataHelper
    {
        /** @var \SprykerTest\Zed\Category\Helper\CategoryDataHelper $categoryDataHelper */
        $categoryDataHelper = $this->getModule('\\' . CategoryDataHelper::class);

        return $categoryDataHelper;
    }

    abstract protected function getModule(string $name): Module;
}
