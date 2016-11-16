<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Acceptance\Category\Category\Zed\PageObject;

class CategoryListPage
{

    const URL = '/category/root';

    const SELECTOR_TABLE = 'dataTables_wrapper';
    const SELECTOR_CATEGORIES_LIST = 'categories-list';

    const BUTTON_CREATE_CATEGORY = 'Create category';

    const SELECTOR_TREE_LIST = '#category-tree > div.dd > ol.dd-list';

}
