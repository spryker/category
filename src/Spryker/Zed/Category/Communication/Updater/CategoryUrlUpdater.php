<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Communication\Updater;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Category\Business\Generator\UrlPathGenerator;
use Spryker\Zed\Category\CategoryConfig;

class CategoryUrlUpdater implements CategoryUrlUpdaterInterface
{
    protected CategoryConfig $categoryConfig;

    public function __construct(CategoryConfig $categoryConfig)
    {
        $this->categoryConfig = $categoryConfig;
    }

    public function updateCategoryUrlPath(array $paths, LocaleTransfer $localeTransfer): array
    {
        $urlLocalizedPrefix = $this->getUrlLocalizedPrefix($localeTransfer);
        array_unshift(
            $paths,
            [
                UrlPathGenerator::CATEGORY_NAME => $urlLocalizedPrefix,
            ],
        );

        return $paths;
    }

    protected function getUrlLocalizedPrefix(LocaleTransfer $localeTransfer): string
    {
        if (!$this->categoryConfig->isFullLocaleNamesInUrlEnabled()) {
            return $this->getLanguageIdentifierFromLocale($localeTransfer);
        }

        return str_replace('_', '-', strtolower($localeTransfer->getLocaleNameOrFail()));
    }

    protected function getLanguageIdentifierFromLocale(LocaleTransfer $localeTransfer): string
    {
        return mb_substr($localeTransfer->getLocaleNameOrFail(), 0, 2);
    }
}
