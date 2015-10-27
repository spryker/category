<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Unit\SprykerFeature\Zed\Category\Business\Tree\Fixtures\Expected;

class CategoryStructureExpected
{

    /**
     * @return array
     */
    public function getOrderedCategoriesArray()
    {
        $categories = [
            1 => [
                'id' => 1,
                'parent' => 0,
                'text' => 'Category 1',
                'children' => [
                    2 => [
                        'id' => 2,
                        'parent' => 1,
                        'text' => 'Category 2',
                        'children' => [
                            3 => [
                                'id' => 3,
                                'parent' => 2,
                                'text' => 'Category 3',
                                'children' => [
                                    5 => [
                                        'id' => 5,
                                        'parent' => 3,
                                        'text' => 'Category 5',
                                        'children' => [
                                            6 => [
                                                'id' => 6,
                                                'parent' => 5,
                                                'text' => 'Category 6',
                                                'children' => [],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    4 => [
                        'id' => 4,
                        'parent' => 1,
                        'text' => 'Category 4',
                        'children' => [],
                    ],
                ],
            ],
        ];

        return $categories;
    }

    /**
     * @return array
     */
    public function getSecondOrderedCategoriesArray()
    {
        $categories = [
            1 => [
                'id' => 1,
                'parent' => 0,
                'text' => 'Category 1',
                'children' => [
                    2 => [
                        'id' => 2,
                        'parent' => 1,
                        'text' => 'Category 2',
                        'children' => [
                            3 => [
                                'id' => 3,
                                'parent' => 2,
                                'text' => 'Category 3',
                                'children' => [],
                            ],
                            5 => [
                                'id' => 5,
                                'parent' => 2,
                                'text' => 'Category 5',
                                'children' => [],
                            ],
                        ],
                    ],
                    4 => [
                        'id' => 4,
                        'parent' => 1,
                        'text' => 'Category 4',
                        'children' => [
                            6 => [
                                'id' => 6,
                                'parent' => 4,
                                'text' => 'Category 6',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $categories;
    }

    /**
     * @return array
     */
    public function getCategoryStructureWithChildrenBeforeParent()
    {
        $categories = [
            1 => [
                'id' => 1,
                'parent' => 0,
                'text' => 'Category 1',
                'children' => [
                    2 => [
                        'id' => 2,
                        'parent' => 1,
                        'text' => 'Category 2',
                        'children' => [
                            5 => [
                                'id' => 5,
                                'parent' => 2,
                                'text' => 'Category 5',
                                'children' => [],
                            ],
                        ],
                    ],
                    4 => [
                        'id' => 4,
                        'parent' => 1,
                        'text' => 'Category 4',
                        'children' => [],
                    ],
                    6 => [
                        'id' => 6,
                        'parent' => 1,
                        'text' => 'Category 6',
                        'children' => [
                            3 => [
                                'id' => 3,
                                'parent' => 6,
                                'text' => 'Category 3',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $categories;
    }

    /**
     * @return array
     */
    public function getCategoryStructureWithNonexistantParent()
    {
        $categories = [
            1 => [
                'id' => 1,
                'parent' => 0,
                'text' => 'Category 1',
                'children' => [
                    2 => [
                        'id' => 2,
                        'parent' => 1,
                        'text' => 'Category 2',
                        'children' => [
                            5 => [
                                'id' => 5,
                                'parent' => 2,
                                'text' => 'Category 5',
                                'children' => [],
                            ],
                            6 => [
                                'id' => 6,
                                'parent' => 2,
                                'text' => 'Category 6',
                                'children' => [],
                            ],
                        ],
                    ],
                    4 => [
                        'id' => 4,
                        'parent' => 1,
                        'text' => 'Category 4',
                        'children' => [],
                    ],
                ],
            ],
        ];

        return $categories;
    }

}
