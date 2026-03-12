<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence;

use Orm\Zed\Category\Persistence\SpyCategoryAttributeQuery;
use Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery;
use Orm\Zed\Category\Persistence\SpyCategoryNodeQuery;
use Orm\Zed\Category\Persistence\SpyCategoryQuery;
use Orm\Zed\Category\Persistence\SpyCategoryStoreQuery;
use Orm\Zed\Category\Persistence\SpyCategoryTemplateQuery;
use Orm\Zed\Url\Persistence\SpyUrlQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\Category\CategoryDependencyProvider;
use Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryLocalizedAttributeMapper;
use Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryLocalizedAttributesUrlMapper;
use Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryMapper;
use Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryMapperInterface;
use Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryNodeMapper;
use Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryStoreRelationMapper;
use Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryTemplateMapper;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use Spryker\Zed\Locale\Business\LocaleFacadeInterface;
use Spryker\Zed\Store\Business\StoreFacadeInterface;

/**
 * @method \Spryker\Zed\Category\CategoryConfig getConfig()
 * @method \Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Category\Persistence\CategoryRepositoryInterface getRepository()
 * @method \Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface getEntityManager()
 */
class CategoryPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @param string|null $modelAlias
     * @param \Propel\Runtime\ActiveQuery\Criteria|null $criteria
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryNodeQuery
     */
    public function createCategoryNodeQuery($modelAlias = null, ?Criteria $criteria = null)
    {
        return SpyCategoryNodeQuery::create($modelAlias, $criteria);
    }

    /**
     * @param string|null $modelAlias
     * @param \Propel\Runtime\ActiveQuery\Criteria|null $criteria
     *
     * @return \Orm\Zed\Url\Persistence\SpyUrlQuery
     */
    public function createUrlQuery($modelAlias = null, ?Criteria $criteria = null)
    {
        return SpyUrlQuery::create($modelAlias, $criteria);
    }

    /**
     * @param string|null $modelAlias
     * @param \Propel\Runtime\ActiveQuery\Criteria|null $criteria
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryAttributeQuery
     */
    public function createCategoryAttributeQuery($modelAlias = null, ?Criteria $criteria = null)
    {
        return SpyCategoryAttributeQuery::create($modelAlias, $criteria);
    }

    /**
     * @param string|null $modelAlias
     * @param \Propel\Runtime\ActiveQuery\Criteria|null $criteria
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryQuery
     */
    public function createCategoryQuery($modelAlias = null, ?Criteria $criteria = null)
    {
        return SpyCategoryQuery::create($modelAlias, $criteria);
    }

    /**
     * @param string|null $modelAlias
     * @param \Propel\Runtime\ActiveQuery\Criteria|null $criteria
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategoryClosureTableQuery
     */
    public function createCategoryClosureTableQuery($modelAlias = null, ?Criteria $criteria = null)
    {
        return SpyCategoryClosureTableQuery::create($modelAlias, $criteria);
    }

    /**
     * @return \Orm\Zed\Category\Persistence\SpyCategoryTemplateQuery
     */
    public function createCategoryTemplateQuery()
    {
        return SpyCategoryTemplateQuery::create();
    }

    public function createCategoryStoreQuery(): SpyCategoryStoreQuery
    {
        return SpyCategoryStoreQuery::create();
    }

    public function createCategoryMapper(): CategoryMapperInterface
    {
        return new CategoryMapper(
            $this->createCategoryNodeMapper(),
            $this->createCategoryStoreRelationMapper(),
            $this->createCategoryLocalizedAttributesUrlMapper(),
            $this->createCategoryTemplateMapper(),
            $this->createCategoryTemplateQuery(),
        );
    }

    public function createCategoryTemplateMapper(): CategoryTemplateMapper
    {
        return new CategoryTemplateMapper();
    }

    public function createCategoryLocalizedAttributeMapper(): CategoryLocalizedAttributeMapper
    {
        return new CategoryLocalizedAttributeMapper(
            $this->getLocaleFacade(),
        );
    }

    public function createCategoryNodeMapper(): CategoryNodeMapper
    {
        return new CategoryNodeMapper();
    }

    public function createCategoryStoreRelationMapper(): CategoryStoreRelationMapper
    {
        return new CategoryStoreRelationMapper(
            $this->getStoreFacade(),
        );
    }

    public function createCategoryLocalizedAttributesUrlMapper(): CategoryLocalizedAttributesUrlMapper
    {
        return new CategoryLocalizedAttributesUrlMapper();
    }

    public function getLocaleFacade(): LocaleFacadeInterface
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_LOCALE);
    }

    public function getStoreFacade(): StoreFacadeInterface
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_STORE);
    }
}
