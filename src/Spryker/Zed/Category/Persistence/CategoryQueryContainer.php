<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Category\Persistence;

use Orm\Zed\Category\Persistence\Map\SpyCategoryAttributeTableMap;
use Orm\Zed\Category\Persistence\Map\SpyCategoryClosureTableTableMap;
use Orm\Zed\Category\Persistence\Map\SpyCategoryNodeTableMap;
use Orm\Zed\Category\Persistence\Map\SpyCategoryTableMap;
use Orm\Zed\Category\Persistence\SpyCategoryAttributeQuery;
use Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery;
use Orm\Zed\Category\Persistence\SpyCategoryNodeQuery;
use Orm\Zed\Category\Persistence\SpyCategoryQuery;
use Spryker\Zed\Kernel\Persistence\AbstractQueryContainer;
use Spryker\Zed\Propel\Business\Formatter\PropelArraySetFormatter;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Orm\Zed\Locale\Persistence\Map\SpyLocaleTableMap;
use Orm\Zed\Url\Persistence\Map\SpyUrlTableMap;
use Orm\Zed\Url\Persistence\SpyUrlQuery;

class CategoryQueryContainer extends AbstractQueryContainer implements CategoryQueryContainerInterface
{

    /**
     * @param int $idLocale
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryNodeWithDirectParent($idLocale)
    {
        $query = SpyCategoryNodeQuery::create()
            ->addJoin(
                SpyCategoryNodeTableMap::COL_FK_CATEGORY,
                SpyCategoryAttributeTableMap::COL_FK_CATEGORY,
                Criteria::INNER_JOIN
            );
        $query->addJoinObject(
            (
            new Join(
                SpyCategoryNodeTableMap::COL_FK_PARENT_CATEGORY_NODE,
                SpyCategoryNodeTableMap::COL_ID_CATEGORY_NODE,
                Criteria::LEFT_JOIN
            )
            )->setRightTableAlias('parent'),
            'parentJoin'
        );
        $query->addJoinObject(
            (
            new Join(
                'parent.fk_category',
                SpyCategoryAttributeTableMap::COL_FK_CATEGORY,
                Criteria::LEFT_JOIN
            )
            )->setRightTableAlias('parentAttributes'),
            'parentAttributesJoin'
        );
        $query->addAnd(
            SpyCategoryAttributeTableMap::COL_FK_LOCALE,
            $idLocale,
            Criteria::EQUAL
        );
        $query->addCond(
            'parentAttributesJoin',
            SpyCategoryAttributeTableMap::COL_FK_LOCALE . '=' .
            $idLocale
        );
        $query->withColumn(SpyCategoryAttributeTableMap::COL_NAME, 'category_name')
            ->withColumn('parentAttributes.name', 'parent_category_name');

        return $query;
    }

    /**
     * @param int $idNode
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryNodeById($idNode)
    {
        return SpyCategoryNodeQuery::create()->filterByIdCategoryNode($idNode);
    }

    /**
     * @param int $idNode
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryFirstLevelChildren($idNode)
    {
        return SpyCategoryNodeQuery::create()
            ->filterByFkParentCategoryNode($idNode)
            ->orderBy(SpyCategoryNodeTableMap::COL_NODE_ORDER, Criteria::DESC);
    }

    /**
     * @param int $idCategory
     * @param int $idParentNode
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryNodeByIdCategoryAndParentNode($idCategory, $idParentNode)
    {
        return SpyCategoryNodeQuery::create()
            ->filterByFkParentCategoryNode($idParentNode)
            ->where(
                SpyCategoryNodeTableMap::COL_FK_CATEGORY . ' = ?',
                $idCategory
            );
    }

    /**
     * @return \Orm\Zed\Category\Persistence\SpyCategoryAttributeQuery
     */
    public function queryRootNodes()
    {
        return SpyCategoryAttributeQuery::create()
            ->joinLocale()
            ->addJoin(
                SpyCategoryAttributeTableMap::COL_FK_CATEGORY,
                SpyCategoryNodeTableMap::COL_FK_CATEGORY,
                Criteria::INNER_JOIN
            )
            ->addAnd(
                SpyCategoryNodeTableMap::COL_IS_ROOT,
                true,
                Criteria::EQUAL
            )
            ->withColumn(
                SpyLocaleTableMap::COL_LOCALE_NAME,
                'locale_name'
            )
            ->withColumn(
                SpyCategoryNodeTableMap::COL_ID_CATEGORY_NODE,
                'id_category_node'
            );
    }

    /**
     * @param int $idNode
     * @param int $idLocale
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryFirstLevelChildrenByIdLocale($idNode, $idLocale)
    {
        $nodeQuery = SpyCategoryNodeQuery::create()
            ->joinParentCategoryNode('parentNode')
            ->addJoin(
                SpyCategoryNodeTableMap::COL_FK_CATEGORY,
                SpyCategoryAttributeTableMap::COL_FK_CATEGORY,
                Criteria::INNER_JOIN
            )
            ->addAnd(
                SpyCategoryAttributeTableMap::COL_FK_LOCALE,
                $idLocale,
                Criteria::EQUAL
            )
            ->addAnd(
                SpyCategoryNodeTableMap::COL_FK_PARENT_CATEGORY_NODE,
                $idNode,
                Criteria::EQUAL
            )
            ->orderBy(SpyCategoryNodeTableMap::COL_NODE_ORDER, Criteria::DESC);

        return $nodeQuery;
    }

    /**
     * @param int $idNode
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery
     */
    public function queryClosureTableByNodeId($idNode)
    {
        $query = SpyCategoryClosureTableQuery::create();

        return $query->where(
            SpyCategoryClosureTableTableMap::COL_FK_CATEGORY_NODE . ' = ?',
            $idNode
        )->_or()->where(
            SpyCategoryClosureTableTableMap::COL_FK_CATEGORY_NODE_DESCENDANT . ' = ?',
            $idNode
        );
    }

    /**
     * @param int $idNode
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery
     */
    public function queryClosureTableParentEntries($idNode)
    {
        $query = SpyCategoryClosureTableQuery::create('node');

        $joinCategoryNodeDescendant = new Join(
            'node.fk_category_node_descendant',
            'descendants.fk_category_node_descendant',
            Criteria::JOIN
        );
        $joinCategoryNodeDescendant
            ->setRightTableName('spy_category_closure_table')
            ->setRightTableAlias('descendants')
            ->setLeftTableName('spy_category_closure_table')
            ->setLeftTableAlias('node');

        $joinCategoryNodeAscendant = new Join(
            'descendants.fk_category_node',
            'ascendants.fk_category_node',
            Criteria::LEFT_JOIN
        );

        $joinCategoryNodeAscendant
            ->setRightTableName('spy_category_closure_table')
            ->setRightTableAlias('ascendants')
            ->setLeftTableName('spy_category_closure_table')
            ->setLeftTableAlias('descendants');

        $query->addJoinObject($joinCategoryNodeDescendant);
        $query->addJoinObject($joinCategoryNodeAscendant, 'ascendantsJoin');

        $query->addJoinCondition(
            'ascendantsJoin',
            'ascendants.fk_category_node_descendant = node.fk_category_node'
        );

        $query
            ->where(
                'descendants.fk_category_node = ' . $idNode
            )
            ->where(
                'ascendants.fk_category_node IS NULL'
            );

        return $query;
    }

    /**
     * @param int $idNode
     *
     * @return self|\Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery
     */
    public function queryClosureTableFilterByIdNode($idNode)
    {
        return SpyCategoryClosureTableQuery::create()
            ->filterByFkCategoryNode($idNode);
    }

    /**
     * @param int $idNodeDescendant
     *
     * @return self|\Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery
     */
    public function queryClosureTableFilterByIdNodeDescendant($idNodeDescendant)
    {
        return SpyCategoryClosureTableQuery::create()
            ->filterByFkCategoryNodeDescendant($idNodeDescendant);
    }

    /**
     * @param int $idNode
     * @param string $idLocale
     * @param bool $onlyOneLevel
     * @param bool $excludeStartNode
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryChildren($idNode, $idLocale, $onlyOneLevel = true, $excludeStartNode = true)
    {
        $nodeQuery = SpyCategoryNodeQuery::create();
        $nodeQuery
            ->useCategoryQuery()
                ->useAttributeQuery()
                    ->filterByFkLocale($idLocale)
                ->endUse()
            ->endUse()
            ->useDescendantQuery()
                ->filterByFkCategoryNode($idNode)
            ->endUse()
            ->withColumn(SpyCategoryNodeTableMap::COL_ID_CATEGORY_NODE)
            ->withColumn(SpyCategoryNodeTableMap::COL_FK_PARENT_CATEGORY_NODE)
            ->withColumn(SpyCategoryAttributeTableMap::COL_NAME)
            ->withColumn(SpyCategoryTableMap::COL_CATEGORY_KEY);

        if ($excludeStartNode) {
            $nodeQuery->filterByIdCategoryNode($idNode, Criteria::NOT_EQUAL);
        }

        if ($onlyOneLevel) {
            $nodeQuery->filterByIdCategoryNode($idNode)
                ->_or();
            $nodeQuery->filterByFkParentCategoryNode($idNode);
        }

        $nodeQuery->orderBy(SpyCategoryNodeTableMap::COL_NODE_ORDER, Criteria::DESC);

        return $nodeQuery;
    }

    /**
     * @param int $idNode
     * @param int $idLocale
     * @param bool $excludeRootNode
     * @param bool $onlyParents
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryPath($idNode, $idLocale, $excludeRootNode = true, $onlyParents = false)
    {
        $depth = null;

        if ($onlyParents) {
            $depth = 0;
        }

        $nodeQuery = SpyCategoryNodeQuery::create();

        if ($excludeRootNode) {
            $nodeQuery->filterByIsRoot(0);
        }

        $nodeQuery
            ->useClosureTableQuery()
                ->orderByFkCategoryNodeDescendant(Criteria::DESC)
                ->orderByDepth(Criteria::DESC)
                ->filterByFkCategoryNodeDescendant($idNode)
                ->filterByDepth($depth, Criteria::NOT_EQUAL)
            ->endUse()
            ->useCategoryQuery()
                ->useAttributeQuery()
                    ->filterByFkLocale($idLocale)
                ->endUse()
            ->endUse();

        $nodeQuery
            ->withColumn(SpyCategoryNodeTableMap::COL_FK_CATEGORY, 'fk_category')
            ->withColumn(SpyCategoryNodeTableMap::COL_ID_CATEGORY_NODE, 'id_category_node')
            ->withColumn(SpyCategoryClosureTableTableMap::COL_FK_CATEGORY_NODE_DESCENDANT, 'fk_category_node_descendant')
            ->withColumn(SpyCategoryAttributeTableMap::COL_NAME, 'name')
            ->withColumn(SpyCategoryTableMap::COL_CATEGORY_KEY, 'category_key')
            ->withColumn(SpyCategoryAttributeTableMap::COL_FK_LOCALE, 'fk_locale');

        $nodeQuery->setFormatter(new PropelArraySetFormatter());

        return $nodeQuery;
    }

    /**
     * @param int $idParentNode
     * @param bool $excludeRoot
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery
     */
    public function getChildrenPath($idParentNode, $excludeRoot = true)
    {
        $query = new SpyCategoryClosureTableQuery();
        $query->filterByFkCategoryNode($idParentNode)
            ->innerJoinNode()
            ->where(SpyCategoryClosureTableTableMap::COL_DEPTH . '> ?', 0);

        if ($excludeRoot) {
            $query->where(SpyCategoryNodeTableMap::COL_IS_ROOT . ' = false');
        }

        return $query;
    }

    /**
     * @param int $idChildNode
     * @param int $idLocale
     * @param bool $excludeRoot
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery
     */
    public function getParentPath($idChildNode, $idLocale, $excludeRoot = true)
    {
        $query = new SpyCategoryClosureTableQuery();
        $query->filterByFkCategoryNodeDescendant($idChildNode)
            ->innerJoinNode()
            ->useNodeQuery()
            ->innerJoinCategory()
            ->useCategoryQuery()
            ->innerJoinAttribute()
                ->addAnd(SpyCategoryAttributeTableMap::COL_FK_LOCALE, $idLocale)
            ->endUse()
            ->endUse()
            ->withColumn(SpyCategoryAttributeTableMap::COL_NAME, 'name')
            ->withColumn(SpyCategoryTableMap::COL_CATEGORY_KEY, 'category_key')
            ->orderBy(SpyCategoryClosureTableTableMap::COL_DEPTH, 'DESC');

        if ($excludeRoot) {
            $query->where(SpyCategoryNodeTableMap::COL_IS_ROOT . ' = false');
        }

        return $query;
    }

    /**
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryRootNode()
    {
        return SpyCategoryNodeQuery::create()
            ->filterByIsRoot(true)
            ->orderBy(SpyCategoryNodeTableMap::COL_NODE_ORDER, Criteria::DESC);
    }

    /**
     * @param int $idNode
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery
     */
    public function queryDescendant($idNode)
    {
        return SpyCategoryClosureTableQuery::create()->filterByFkCategoryNode($idNode);
    }

    /**
     * @param int $idCategory
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryAttributeQuery
     */
    public function queryAttributeByCategoryId($idCategory)
    {
        return SpyCategoryAttributeQuery::create()->filterByFkCategory($idCategory);
    }

    /**
     * @param int $idCategory
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryAllNodesByCategoryId($idCategory)
    {
        return $this->queryNodesByCategoryId($idCategory, null);
    }

    /**
     * @param int $idCategory
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryMainNodesByCategoryId($idCategory)
    {
        return $this->queryNodesByCategoryId($idCategory, true);
    }

    /**
     * @param int $idCategory
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryNotMainNodesByCategoryId($idCategory)
    {
        return $this->queryNodesByCategoryId($idCategory, false);
    }

    /**
     * @param int $idCategory
     * @param mixed $isMain true|false|null
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    protected function queryNodesByCategoryId($idCategory, $isMain)
    {
        $query = SpyCategoryNodeQuery::create()
            ->filterByFkCategory($idCategory);

        if ($isMain !== null) {
            $query->filterByIsMain($isMain);
        }

        return $query;
    }

    /**
     * @param int $idCategory
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryQuery
     */
    public function queryCategoryById($idCategory)
    {
        return SpyCategoryQuery::create()->filterByIdCategory($idCategory);
    }

    /**
     * @param int $idNode
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryCategoryNodeByNodeId($idNode)
    {
        return SpyCategoryNodeQuery::create()->filterByIdCategoryNode($idNode);
    }

    /**
     * @param int $idLocale
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryQuery
     */
    public function queryCategory($idLocale)
    {
        return SpyCategoryQuery::create()
            ->joinAttribute()
            ->innerJoinNode()
            ->addAnd(
                SpyCategoryAttributeTableMap::COL_FK_LOCALE,
                $idLocale,
                Criteria::EQUAL
            )
            ->withColumn(SpyCategoryTableMap::COL_ID_CATEGORY, 'id_category')
            ->withColumn(SpyCategoryAttributeTableMap::COL_NAME, 'name')
            ->withColumn(SpyCategoryTableMap::COL_CATEGORY_KEY, 'category_key')
            ->withColumn(SpyCategoryNodeTableMap::COL_ID_CATEGORY_NODE, 'id_category_node');
    }

    /**
     * @param int $idCategory
     * @param int $idLocale
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryAttributeQuery
     */
    public function queryAttributeByCategoryIdAndLocale($idCategory, $idLocale)
    {
        return SpyCategoryAttributeQuery::create()
            ->joinLocale()
            ->filterByFkLocale($idLocale)
            ->filterByFkCategory($idCategory)
            ->withColumn(SpyLocaleTableMap::COL_LOCALE_NAME);
    }

    /**
     * @param string $name
     * @param int $idLocale
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryAttributeQuery
     */
    public function queryCategoryAttributesByName($name, $idLocale)
    {
        return SpyCategoryAttributeQuery::create()
            ->filterByName($name)
            ->filterByFkLocale($idLocale);
    }

    /**
     * @param int $idLocale
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryCategoryNode($idLocale)
    {
        $nodeQuery = SpyCategoryNodeQuery::create();
        $nodeQuery->useCategoryQuery()
            ->useAttributeQuery()
            ->filterByFkLocale($idLocale)
            ->endUse()
            ->endUse();
        $nodeQuery
            ->withColumn(SpyCategoryNodeTableMap::COL_ID_CATEGORY_NODE, 'id_category_node')
            ->withColumn(SpyCategoryAttributeTableMap::COL_NAME, 'category_name');

        return $nodeQuery;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $expandableQuery
     * @param string $rightTableAlias
     * @param string $fieldIdentifier
     * @param string $leftTableAlias
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function joinCategoryQueryWithChildrenCategories(
        ModelCriteria $expandableQuery,
        $rightTableAlias = 'categoryChildren',
        $fieldIdentifier = 'child',
        $leftTableAlias = SpyCategoryNodeTableMap::TABLE_NAME
    ) {
        $expandableQuery
            ->addJoinObject(
                (new Join(
                    $leftTableAlias . '.id_category_node',
                    SpyCategoryNodeTableMap::COL_FK_PARENT_CATEGORY_NODE,
                    Criteria::LEFT_JOIN
                ))->setRightTableAlias($rightTableAlias)
            );

        $expandableQuery->withColumn(
            'GROUP_CONCAT(' . $rightTableAlias . '.id_category_node)',
            'category_' . $fieldIdentifier . '_ids'
        );

        return $expandableQuery;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $expandableQuery
     * @param bool $excludeDirectParent
     * @param bool $excludeRoot
     * @param string $leftTableAlias
     * @param string $relationTableAlias
     * @param string $fieldIdentifier
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function joinCategoryQueryWithParentCategories(
        ModelCriteria $expandableQuery,
        $excludeDirectParent = true,
        $excludeRoot = true,
        $leftTableAlias = SpyCategoryNodeTableMap::TABLE_NAME,
        $relationTableAlias = 'categoryParents',
        $fieldIdentifier = 'parent'
    ) {
        $expandableQuery
            ->addJoinObject(
                (new Join(
                    $leftTableAlias . '.id_category_node',
                    SpyCategoryClosureTableTableMap::COL_FK_CATEGORY_NODE_DESCENDANT,
                    Criteria::LEFT_JOIN
                ))
            );

        $expandableQuery
            ->addJoinObject(
                (new Join(
                    SpyCategoryClosureTableTableMap::COL_FK_CATEGORY_NODE,
                    SpyCategoryNodeTableMap::COL_ID_CATEGORY_NODE,
                    Criteria::INNER_JOIN
                ))->setRightTableAlias($relationTableAlias),
                $relationTableAlias . 'Join'
            );

        if ($excludeDirectParent) {
            $expandableQuery->addAnd(
                SpyCategoryClosureTableTableMap::COL_DEPTH,
                0,
                Criteria::GREATER_THAN
            );
        }

        if ($excludeRoot) {
            $expandableQuery->addJoinCondition(
                $relationTableAlias . 'Join',
                $relationTableAlias . '.is_root = false'
            );
        }

        $expandableQuery->withColumn(
            'GROUP_CONCAT(' . $relationTableAlias . '.id_category_node)',
            'category_' . $fieldIdentifier . '_ids'
        );
        $expandableQuery->withColumn(
            SpyCategoryClosureTableTableMap::COL_FK_CATEGORY_NODE_DESCENDANT,
            'descendant_id'
        );
        $expandableQuery->withColumn(
            SpyCategoryClosureTableTableMap::COL_DEPTH,
            'depth'
        );

        return $expandableQuery;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $expandableQuery
     * @param string $leftAlias
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function joinCategoryQueryWithUrls(
        ModelCriteria $expandableQuery,
        $leftAlias = SpyCategoryNodeTableMap::TABLE_NAME
    ) {
        $expandableQuery
            ->addJoinObject(
                (new Join(
                    $leftAlias . '.id_category_node',
                    SpyUrlTableMap::COL_FK_RESOURCE_CATEGORYNODE,
                    Criteria::LEFT_JOIN
                ))->setRightTableAlias('categoryUrls'),
                'categoryUrlJoin'
            );

        $expandableQuery->addJoinCondition(
            'categoryUrlJoin',
            'categoryUrls.fk_locale = ' .
            SpyLocaleTableMap::COL_ID_LOCALE
        );

        $expandableQuery->withColumn(
            'GROUP_CONCAT(categoryUrls.url)',
            'category_urls'
        );

        return $expandableQuery;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $expandableQuery
     * @param string $relationTableAlias
     * @param string $fieldIdentifier
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function joinLocalizedRelatedCategoryQueryWithAttributes(
        ModelCriteria $expandableQuery,
        $relationTableAlias,
        $fieldIdentifier
    ) {
        $expandableQuery->addJoinObject(
            (new Join(
                $relationTableAlias . '.fk_category',
                SpyCategoryAttributeTableMap::COL_FK_CATEGORY,
                Criteria::LEFT_JOIN
            ))->setRightTableAlias($relationTableAlias . 'Attributes'),
            $relationTableAlias . 'AttributesJoin'
        );

        $expandableQuery->addCond(
            $relationTableAlias . 'AttributesJoin',
            SpyCategoryAttributeTableMap::COL_FK_LOCALE . '=' .
            SpyLocaleTableMap::COL_ID_LOCALE
        );

        $expandableQuery->withColumn(
            'GROUP_CONCAT(' . $relationTableAlias . 'Attributes.name)',
            'category_' . $fieldIdentifier . '_names'
        );

        return $expandableQuery;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $expandableQuery
     * @param string $relationTableAlias
     * @param string $fieldIdentifier
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function joinRelatedCategoryQueryWithUrls(
        ModelCriteria $expandableQuery,
        $relationTableAlias,
        $fieldIdentifier
    ) {
        $expandableQuery->addJoinObject(
            (new Join(
                $relationTableAlias . '.id_category_node',
                SpyUrlTableMap::COL_FK_RESOURCE_CATEGORYNODE,
                Criteria::LEFT_JOIN
            ))->setRightTableAlias($relationTableAlias . 'Urls'),
            $relationTableAlias . 'UrlJoin'
        );

        $expandableQuery->addJoinCondition(
            $relationTableAlias . 'UrlJoin',
            $relationTableAlias . 'Urls.fk_locale = ' .
            SpyLocaleTableMap::COL_ID_LOCALE
        );

        $expandableQuery->withColumn(
            'GROUP_CONCAT(' . $relationTableAlias . 'Urls.url)',
            'category_' . $fieldIdentifier . '_urls'
        );

        return $expandableQuery;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $expandableQuery
     * @param string $tableAlias
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function selectCategoryAttributeColumns(
        ModelCriteria $expandableQuery,
        $tableAlias = SpyCategoryAttributeTableMap::TABLE_NAME
    ) {
        $expandableQuery->withColumn(
            $tableAlias . '.name',
            'category_name'
        );
        $expandableQuery->withColumn(
            $tableAlias . '.meta_title',
            'category_meta_title'
        );
        $expandableQuery->withColumn(
            $tableAlias . '.meta_description',
            'category_meta_description'
        );
        $expandableQuery->withColumn(
            $tableAlias . '.meta_keywords',
            'category_meta_keywords'
        );
        $expandableQuery->withColumn(
            $tableAlias . '.category_image_name',
            'category_image_name'
        );

        return $expandableQuery;
    }

    /**
     * @param string $categoryName
     * @param int $idLocale
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryNodeByCategoryName($categoryName, $idLocale)
    {
        $nodeQuery = SpyCategoryNodeQuery::create();
        $nodeQuery
            ->useCategoryQuery()
                ->useAttributeQuery()
                    ->filterByName($categoryName)
                    ->filterByFkLocale($idLocale)
                ->endUse()
            ->endUse();

        return $nodeQuery;
    }

    /**
     * @param string $categoryKey
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function queryNodeByCategoryKey($categoryKey)
    {
        $nodeQuery = SpyCategoryNodeQuery::create();
        $nodeQuery->useCategoryQuery()
            ->filterByCategoryKey($categoryKey)
            ->endUse();

        return $nodeQuery;
    }

    /**
     * @param int $idCategoryNode
     *
     * @return \Orm\Zed\Url\Persistence\SpyUrlQuery
     */
    public function queryUrlByIdCategoryNode($idCategoryNode)
    {
        return SpyUrlQuery::create()
            ->joinSpyLocale()
            ->filterByFkResourceCategorynode($idCategoryNode)
            ->withColumn(SpyLocaleTableMap::COL_LOCALE_NAME);
    }

    /**
     * @param int $idParentNode
     * @param int $idLocale
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function getCategoryNodesWithOrder($idParentNode, $idLocale)
    {
        return SpyCategoryNodeQuery::create()
            ->filterByFkParentCategoryNode($idParentNode)
            ->useCategoryQuery()
                ->innerJoinAttribute()
                ->addAnd(
                    SpyCategoryAttributeTableMap::COL_FK_LOCALE,
                    $idLocale,
                    Criteria::EQUAL
                )
            ->endUse()
            ->withColumn(SpyCategoryAttributeTableMap::COL_NAME)
            ->orderBy(SpyCategoryNodeTableMap::COL_NODE_ORDER, Criteria::DESC);
    }

}
