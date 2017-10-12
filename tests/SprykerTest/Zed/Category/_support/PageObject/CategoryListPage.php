<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category\PageObject;

class CategoryListPage
{
    const URL = '/category/root';

    const SELECTOR_TABLE = 'dataTables_wrapper';
    const SELECTOR_CATEGORIES_LIST = 'categories-list';

    const BUTTON_CREATE_CATEGORY = '//div[@class="title-action"]/a';

    const SELECTOR_TREE_LIST = '#category-tree > div.dd > ol.dd-list';

    /**
     * @param int $position
     *
     * @return string
     */
    public static function getAssignProductsButtonSelector($position = 1)
    {
        return sprintf('//a[@title="Assign Products to this Category"][%s]', $position);
    }

    /**
     * @param int $position
     *
     * @return string
     */
    public static function getDeleteButtonSelector($position = 1)
    {
        return sprintf('//a[@title="Delete Category"][%s]', $position);
    }
}
