<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Communication\Form\DataProvider;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Zed\Category\Dependency\Facade\CategoryToLocaleInterface;

class LocaleProvider
{
    public const DEFAULT_LOCALE = 'default';
    /**
     * @var \Spryker\Zed\Category\Dependency\Facade\CategoryToLocaleInterface
     */
    protected $localeFacade;

    /**
     * @param \Spryker\Zed\Category\Dependency\Facade\CategoryToLocaleInterface $localeFacade
     */
    public function __construct(CategoryToLocaleInterface $localeFacade)
    {
        $this->localeFacade = $localeFacade;
    }

    /**
     * @param bool $includeDefault
     *
     * @return \Generated\Shared\Transfer\LocaleTransfer[]
     */
    public function getLocaleCollection(bool $includeDefault = false): array
    {
        $result = [];

        if ($includeDefault) {
            $result[] = (new LocaleTransfer())
                ->setLocaleName(static::DEFAULT_LOCALE);
        }

        foreach ($this->localeFacade->getLocaleCollection() as $localeCode => $localeTransfer) {
            $result[] = $localeTransfer;
        }

        return $result;
    }

    /**
     * @param string $localeCode
     *
     * @return \Generated\Shared\Transfer\LocaleTransfer
     */
    public function getLocaleTransfer(string $localeCode): LocaleTransfer
    {
        return $this->localeFacade->getLocale($localeCode);
    }

    /**
     * @return \Generated\Shared\Transfer\LocaleTransfer
     */
    public function getCurrentLocale(): LocaleTransfer
    {
        return $this->localeFacade->getCurrentLocale();
    }
}
