<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category\PageObject;

class CategoryCreatePage extends Category
{
    /**
     * @var string
     */
    public const URL = '/category/create';

    /**
     * @var string
     */
    public const FORM_SUBMIT_BUTTON = 'Save';

    /**
     * @var string
     */
    public const SUCCESS_MESSAGE = 'The category was added successfully.';

    /**
     * @var array<string>
     */
    public const CLOSED_IBOX_SELECTORS = [
        '#localizedAttributes-ibox-de_DE .ibox-tools',
    ];

    /**
     * @param string $categoryKey
     *
     * @return array
     */
    public static function getCategorySelectorsWithValues(string $categoryKey): array
    {
        return [
            static::FORM_FIELD_CATEGORY_KEY => $categoryKey,
            static::FORM_FIELD_CATEGORY_PARENT => 1,
            static::FORM_FIELD_CATEGORY_TEMPLATE => 1,
            'attributes' => [
                'en_US' => static::getAttributesSelector($categoryKey, 'en_US', 0),
                'de_DE' => static::getAttributesSelector($categoryKey, 'de_DE', 1),
            ],
        ];
    }

    /**
     * @param string $name
     * @param string $localeName
     * @param int $position
     *
     * @return array
     */
    public static function getAttributesSelector(string $name, string $localeName, int $position): array
    {
        return [
            static::getFieldSelectorCategoryName($position) => $name . ' ' . $localeName,
            static::getFieldSelectorCategoryTitle($position) => $name . ' ' . $localeName . ' Title',
            static::getFieldSelectorCategoryDescription($position) => $name . ' ' . $localeName . ' Description',
            static::getFieldSelectorCategoryKeywords($position) => $name . ' ' . $localeName . ' Keywords',
        ];
    }

    /**
     * @param int $position
     *
     * @return string
     */
    public static function getFieldSelectorCategoryName(int $position): string
    {
        return sprintf(static::FORM_FIELD_CATEGORY_NAME_PATTERN, $position);
    }

    /**
     * @param int $position
     *
     * @return string
     */
    public static function getFieldSelectorCategoryTitle(int $position): string
    {
        return sprintf(static::FORM_FIELD_CATEGORY_TITLE_PATTERN, $position);
    }

    /**
     * @param int $position
     *
     * @return string
     */
    public static function getFieldSelectorCategoryDescription(int $position): string
    {
        return sprintf(static::FORM_FIELD_CATEGORY_DESCRIPTION_PATTERN, $position);
    }

    /**
     * @param int $position
     *
     * @return string
     */
    public static function getFieldSelectorCategoryKeywords(int $position): string
    {
        return sprintf(static::FORM_FIELD_CATEGORY_KEYWORDS_PATTERN, $position);
    }
}
