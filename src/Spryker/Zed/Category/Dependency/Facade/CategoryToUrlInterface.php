<?php

/**
 * (c) Spryker Systems GmbH copyright protected.
 */

namespace Spryker\Zed\Category\Dependency\Facade;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use Propel\Runtime\Exception\PropelException;
use Spryker\Zed\Category\Business\Exception\MissingUrlException;
use Spryker\Zed\Category\Business\Exception\UrlExistsException;

interface CategoryToUrlInterface
{

    /**
     * @param string $url
     * @param LocaleTransfer $locale
     * @param string $resourceType
     * @param int $resourceId
     *
     * @throws PropelException
     * @throws UrlExistsException
     *
     * @return UrlTransfer
     */
    public function createUrl($url, LocaleTransfer $locale, $resourceType, $resourceId);

    /**
     * @param int $idUrl
     */
    public function touchUrlActive($idUrl);

    /**
     * @param int $idUrl
     */
    public function touchUrlDeleted($idUrl);

    /**
     * @param UrlTransfer $urlTransfer
     *
     * @return UrlTransfer
     */
    public function saveUrlAndTouch(UrlTransfer $urlTransfer);

    /**
     * @param string $url
     *
     * @return bool
     */
    public function hasUrl($url);

    /**
     * @param string $urlString
     *
     * @throws MissingUrlException
     *
     * @return UrlTransfer
     */
    public function getUrlByPath($urlString);

    /**
     * @param int $idCategoryNode
     * @param LocaleTransfer $locale
     *
     * @return UrlTransfer
     */
    public function getResourceUrlByCategoryNodeIdAndLocale($idCategoryNode, LocaleTransfer $locale);

    /**
     * @param UrlTransfer $urlTransfer
     *
     * @throws MissingUrlException
     * @throws PropelException
     *
     * @return void
     */
    public function deleteUrl(UrlTransfer $urlTransfer);

}
