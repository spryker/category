<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Acceptance\Category\Category\Zed\PageObject;

class CategoryEditPage extends Category
{

    const URL = '/category/edit?id-category=';
    const TITLE = 'Edit category';
    const SUCCESS_MESSAGE = 'The category was updated successfully.';
    const SUBMIT_BUTTON = 'Save';

    /**
     * @param int $idCategory
     *
     * @return string
     */
    public static function getUrl($idCategory)
    {
        return self::URL . $idCategory;
    }

}
