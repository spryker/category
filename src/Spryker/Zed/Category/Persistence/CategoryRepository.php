<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence;

use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Orm\Zed\Category\Persistence\Map\SpyCategoryAttributeTableMap;
use Orm\Zed\Category\Persistence\SpyCategoryNodeQuery;
use Orm\Zed\Category\Persistence\SpyCategoryQuery;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;
use Spryker\Zed\PropelOrm\Business\Model\Formatter\PropelArraySetFormatter;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;

/**
 * @method \Spryker\Zed\Category\Persistence\CategoryPersistenceFactory getFactory()
 */
class CategoryRepository extends AbstractRepository implements CategoryRepositoryInterface
{
    public const CATEGORY_NODE_PATH_GLUE = ' / ';
    public const NODE_PATH_NULL_DEPTH = null;
    public const IS_NOT_ROOT_NODE = 0;
    protected const COL_CATEGORY_NAME = 'name';

    /**
     * @param int $idNode
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return string
     */
    public function getCategoryNodePath(int $idNode, LocaleTransfer $localeTransfer): string
    {
        $nodePathQuery = $this->queryNodePathWithoutRootNode(
            $idNode,
            $localeTransfer->getIdLocale(),
            static::NODE_PATH_NULL_DEPTH
        );

        return $this->generateNodePathString($nodePathQuery, static::CATEGORY_NODE_PATH_GLUE);
    }

    /**
     * @param \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery $nodePathQuery
     * @param string $glue
     *
     * @return string
     */
    protected function generateNodePathString(SpyCategoryNodeQuery $nodePathQuery, string $glue): string
    {
        $nodePathQuery = $nodePathQuery
            ->clearSelectColumns()
            ->addSelectColumn(static::COL_CATEGORY_NAME);

        /** @var string[] $pathTokens */
        $pathTokens = $nodePathQuery->find();

        return implode($glue, $pathTokens);
    }

    /**
     * @param int $idNode
     * @param int $idLocale
     * @param int|null $depth
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    protected function queryNodePathWithRootNode(
        int $idNode,
        int $idLocale,
        ?int $depth = self::NODE_PATH_NULL_DEPTH
    ): SpyCategoryNodeQuery {
        return $this->getFactory()->createCategoryNodeQuery()
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
            ->endUse()
            ->setFormatter(new PropelArraySetFormatter());
    }

    /**
     * @param int $idNode
     * @param int $idLocale
     * @param int|null $depth
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    protected function queryNodePathWithoutRootNode(
        int $idNode,
        int $idLocale,
        ?int $depth = self::NODE_PATH_NULL_DEPTH
    ): SpyCategoryNodeQuery {
        return $this->queryNodePathWithRootNode($idNode, $idLocale, $depth)
            ->filterByIsRoot(static::IS_NOT_ROOT_NODE);
    }
}
