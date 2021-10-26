<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Dependency;

interface CategoryEvents
{
    /**
     * @var string
     */
    public const CATEGORY_BEFORE_CREATE = 'Category.before.create';

    /**
     * @var string
     */
    public const CATEGORY_BEFORE_UPDATE = 'Category.before.update';

    /**
     * @var string
     */
    public const CATEGORY_BEFORE_DELETE = 'Category.before.delete';

    /**
     * @var string
     */
    public const CATEGORY_AFTER_CREATE = 'Category.after.create';

    /**
     * @var string
     */
    public const CATEGORY_AFTER_UPDATE = 'Category.after.update';

    /**
     * @var string
     */
    public const CATEGORY_AFTER_DELETE = 'Category.after.delete';

    /**
     * Specification:
     * - This event will be used after `Category` creation.
     *
     * @api
     *
     * @var string
     */
    public const CATEGORY_AFTER_PUBLISH_CREATE = 'Category.after.publish_create';

    /**
     * Specification:
     * - This event will be used after `Category` updating.
     *
     * @api
     *
     * @var string
     */
    public const CATEGORY_AFTER_PUBLISH_UPDATE = 'Category.after.publish_update';

    /**
     * Specification:
     * - This event will be used after `Category` deletion.
     *
     * @api
     *
     * @var string
     */
    public const CATEGORY_AFTER_PUBLISH_DELETE = 'Category.after.publish_delete';

    /**
     * Specification:
     * - This events will be used for Category publish
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_CATEGORY_PUBLISH = 'Entity.spy_category.publish';

    /**
     * Specification:
     * - This events will be used for spy_category entity creation
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_CREATE = 'Entity.spy_category.create';

    /**
     * Specification:
     * - This events will be used for spy_category entity changes
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_UPDATE = 'Entity.spy_category.update';

    /**
     * Specification:
     * - This events will be used for spy_category entity deletion
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_DELETE = 'Entity.spy_category.delete';

    /**
     * Specification:
     * - This events will be used for spy_category_attribute entity creation
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_ATTRIBUTE_CREATE = 'Entity.spy_category_attribute.create';

    /**
     * Specification:
     * - This events will be used for spy_category_attribute entity changes
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_ATTRIBUTE_UPDATE = 'Entity.spy_category_attribute.update';

    /**
     * Specification:
     * - This events will be used for spy_category_attribute entity deletion
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_ATTRIBUTE_DELETE = 'Entity.spy_category_attribute.delete';

    /**
     * Specification:
     * - This events will be used for spy_category_closure_table entity creation
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_CLOSURE_TABLE_CREATE = 'Entity.spy_category_closure_table.create';

    /**
     * Specification:
     * - This events will be used for spy_category_closure_table entity changes
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_CLOSURE_TABLE_UPDATE = 'Entity.spy_category_closure_table.update';

    /**
     * Specification:
     * - This events will be used for spy_category_closure_table entity deletion
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_CLOSURE_TABLE_DELETE = 'Entity.spy_category_closure_table.delete';

    /**
     * Specification:
     * - This events will be used for spy_category_node entity creation
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_NODE_CREATE = 'Entity.spy_category_node.create';

    /**
     * Specification:
     * - This events will be used for spy_category_node entity changes
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_NODE_UPDATE = 'Entity.spy_category_node.update';

    /**
     * Specification:
     * - This events will be used for spy_category_node entity deletion
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_NODE_DELETE = 'Entity.spy_category_node.delete';

    /**
     * Specification:
     * - This events will be used for spy_category_template entity creation
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_TEMPLATE_CREATE = 'Entity.spy_category_template.create';

    /**
     * Specification:
     * - This events will be used for spy_category_template entity changes
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_TEMPLATE_UPDATE = 'Entity.spy_category_template.update';

    /**
     * Specification:
     * - This events will be used for spy_category_template entity deletion
     *
     * @api
     *
     * @var string
     */
    public const ENTITY_SPY_CATEGORY_TEMPLATE_DELETE = 'Entity.spy_category_template.delete';

    /**
     * Specification:
     * - This events will be used for CategoryNode publish
     *
     * @api
     *
     * @var string
     */
    public const CATEGORY_NODE_PUBLISH = 'Category.node.publish';

    /**
     * Specification:
     * - This events will be used for CategoryNode publish
     *
     * @api
     *
     * @var string
     */
    public const CATEGORY_NODE_UNPUBLISH = 'Category.node.unpublish';

    /**
     * Specification:
     * - This events will be used for CategoryTree publish
     *
     * @api
     *
     * @var string
     */
    public const CATEGORY_TREE_PUBLISH = 'Category.tree.publish';

    /**
     * Specification:
     * - This events will be used for CategoryTree publish
     *
     * @api
     *
     * @var string
     */
    public const CATEGORY_TREE_UNPUBLISH = 'Category.tree.unpublish';
}
