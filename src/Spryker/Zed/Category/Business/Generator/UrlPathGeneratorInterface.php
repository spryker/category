<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Generator;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\NodeTransfer;

interface UrlPathGeneratorInterface
{
    public function buildCategoryNodeUrlForLocale(NodeTransfer $nodeTransfer, LocaleTransfer $localeTransfer): string;

    public function generate(array $categoryPath): string;

    /**
     * @param array<int> $categoryNodeIds
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return array<string>
     */
    public function bulkBuildCategoryNodeUrlForLocale(array $categoryNodeIds, LocaleTransfer $localeTransfer): array;
}
