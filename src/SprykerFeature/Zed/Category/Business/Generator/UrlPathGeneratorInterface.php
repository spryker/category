<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\Category\Business\Generator;

interface UrlPathGeneratorInterface
{

    /**
     * @param array $categoryPath
     *
     * @return string
     */
    public function generate(array $categoryPath);

}
