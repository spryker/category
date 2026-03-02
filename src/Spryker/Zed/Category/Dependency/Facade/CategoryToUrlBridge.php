<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Dependency\Facade;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\UrlTransfer;

class CategoryToUrlBridge implements CategoryToUrlInterface
{
    /**
     * @var \Spryker\Zed\Url\Business\UrlFacadeInterface
     */
    protected $urlFacade;

    /**
     * @param \Spryker\Zed\Url\Business\UrlFacadeInterface $urlFacade
     */
    public function __construct($urlFacade)
    {
        $this->urlFacade = $urlFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\UrlTransfer|string $urlTransfer Deprecated: String format is accepted for BC reasons only.
     * @param \Generated\Shared\Transfer\LocaleTransfer|null $localeTransfer Deprecated: This parameter exists for BC reasons. Use `createUrl(UrlTransfer $urlTransfer)` format instead.
     * @param string|null $resourceType Deprecated: This parameter exists for BC reasons. Use `createUrl(UrlTransfer $urlTransfer)` format instead.
     * @param int|null $idResource Deprecated: This parameter exists for BC reasons. Use `createUrl(UrlTransfer $urlTransfer)` format instead.
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    public function createUrl($urlTransfer, ?LocaleTransfer $localeTransfer = null, ?string $resourceType = null, ?int $idResource = null): UrlTransfer
    {
        return $this->urlFacade->createUrl($urlTransfer, $localeTransfer, $resourceType, $idResource);
    }

    public function updateUrl(UrlTransfer $urlTransfer): UrlTransfer
    {
        return $this->urlFacade->updateUrl($urlTransfer);
    }

    public function hasUrlCaseInsensitive(UrlTransfer $urlTransfer): bool
    {
        return $this->urlFacade->hasUrlCaseInsensitive($urlTransfer);
    }

    public function deleteUrl(UrlTransfer $urlTransfer): void
    {
        $this->urlFacade->deleteUrl($urlTransfer);
    }
}
