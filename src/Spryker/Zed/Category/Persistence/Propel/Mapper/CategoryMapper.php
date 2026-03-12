<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer;
use Generated\Shared\Transfer\CategoryTemplateTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\NodeCollectionTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Orm\Zed\Category\Persistence\SpyCategory;
use Orm\Zed\Category\Persistence\SpyCategoryNode;
use Orm\Zed\Category\Persistence\SpyCategoryTemplateQuery;
use Propel\Runtime\Collection\ArrayCollection;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;

class CategoryMapper implements CategoryMapperInterface
{
    protected const string CATEGORY = 'Category';

    protected const string COL_FK_CATEGORY_TEMPLATE = 'fk_category_template';

    /**
     * @var \Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryNodeMapper
     */
    protected $categoryNodeMapper;

    /**
     * @var \Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryStoreRelationMapper
     */
    protected $categoryStoreRelationMapper;

    /**
     * @var \Spryker\Zed\Category\Persistence\Propel\Mapper\CategoryLocalizedAttributesUrlMapper
     */
    protected $categoryLocalizedAttributesUrlMapper;

    protected CategoryTemplateMapper $categoryTemplateMapper;

    protected SpyCategoryTemplateQuery $categoryTemplateQuery;

    /**
     * @var array<int, \Generated\Shared\Transfer\CategoryTemplateTransfer>
     */
    protected static array $categoryTemplateCache = [];

    public function __construct(
        CategoryNodeMapper $categoryNodeMapper,
        CategoryStoreRelationMapper $categoryStoreRelationMapper,
        CategoryLocalizedAttributesUrlMapper $categoryLocalizedAttributesUrlMapper,
        CategoryTemplateMapper $categoryTemplateMapper,
        SpyCategoryTemplateQuery $categoryTemplateQuery
    ) {
        $this->categoryNodeMapper = $categoryNodeMapper;
        $this->categoryStoreRelationMapper = $categoryStoreRelationMapper;
        $this->categoryLocalizedAttributesUrlMapper = $categoryLocalizedAttributesUrlMapper;
        $this->categoryTemplateMapper = $categoryTemplateMapper;
        $this->categoryTemplateQuery = $categoryTemplateQuery;
    }

    public function mapCategory(SpyCategory $spyCategory, CategoryTransfer $categoryTransfer): CategoryTransfer
    {
        return $categoryTransfer->fromArray($spyCategory->toArray(), true);
    }

    public function mapCategoryWithRelations(SpyCategory $spyCategory, CategoryTransfer $categoryTransfer): CategoryTransfer
    {
        $categoryTransfer = $this->mapCategory($spyCategory, $categoryTransfer);
        $categoryTransfer = $this->mapParentCategoryNodes($spyCategory, $categoryTransfer);
        $categoryTransfer = $this->mapLocalizedAttributes($spyCategory->getAttributes(), $categoryTransfer);
        $categoryTransfer->setCategoryTemplate($this->categoryTemplateMapper->mapCategoryTemplateEntityToCategoryTemplateTransfer(
            $spyCategory->getCategoryTemplate(),
            new CategoryTemplateTransfer(),
        ));
        $categoryTransfer = $this->categoryNodeMapper->mapCategoryNodes($spyCategory, $categoryTransfer);
        $storeRelationTransfer = $this->categoryStoreRelationMapper->mapCategoryStoreEntitiesToStoreRelationTransfer(
            $spyCategory->getSpyCategoryStores(),
            (new StoreRelationTransfer())->setIdEntity($spyCategory->getIdCategory()),
        );
        $categoryTransfer->setStoreRelation($storeRelationTransfer);

        return $categoryTransfer;
    }

    public function mapCategoryNodeEntitiesToNodeCollectionTransfer(
        ArrayCollection $categoryNodeArray,
        NodeCollectionTransfer $nodeCollectionTransfer
    ): NodeCollectionTransfer {
        foreach ($categoryNodeArray as $categoryNodeData) {
            $nodeTransfer = $this->mapCategoryNodeDataToNodeTransferWithCategoryTemplates(
                $categoryNodeData,
                new NodeTransfer(),
            );

            $nodeCollectionTransfer->addNode($nodeTransfer);
        }

        return $nodeCollectionTransfer;
    }

    public function mapCategoryNodeEntityToNodeTransferWithCategoryRelation(SpyCategoryNode $nodeEntity, NodeTransfer $nodeTransfer): NodeTransfer
    {
        $nodeTransfer = $this->mapCategoryNodeEntityToNodeTransferWithCategoryTemplates($nodeEntity, $nodeTransfer);

        $categoryEntity = $nodeEntity->getCategory();

        $categoryTransfer = $this->mapLocalizedAttributes(
            $categoryEntity->getAttributes(),
            $nodeTransfer->getCategoryOrFail(),
            $nodeEntity->getSpyUrls(),
        );

        $storeRelationTransfer = $this->categoryStoreRelationMapper->mapCategoryStoreEntitiesToStoreRelationTransfer(
            $categoryEntity->getSpyCategoryStores(),
            (new StoreRelationTransfer())->setIdEntity($categoryEntity->getIdCategory()),
        );
        $categoryTransfer->setStoreRelation($storeRelationTransfer);

        return $nodeTransfer->setCategory($categoryTransfer);
    }

    public function mapCategoryNodeEntityToNodeTransferWithCategoryTemplates(SpyCategoryNode $nodeEntity, NodeTransfer $nodeTransfer): NodeTransfer
    {
        $nodeTransfer = $this->categoryNodeMapper->mapCategoryNode($nodeEntity, $nodeTransfer);
        $categoryEntity = $nodeEntity->getCategory();

        $categoryTransfer = $this->mapCategory($categoryEntity, new CategoryTransfer());

        $categoryTemplateTransfer = $this->categoryTemplateMapper->mapCategoryTemplateEntityToCategoryTemplateTransfer(
            $categoryEntity->getCategoryTemplate(),
            new CategoryTemplateTransfer(),
        );
        $categoryTransfer->setCategoryTemplate($categoryTemplateTransfer);

        return $nodeTransfer->setCategory($categoryTransfer);
    }

    /**
     * @param array<string, mixed> $categoryNodeData
     * @param \Generated\Shared\Transfer\NodeTransfer $nodeTransfer
     *
     * @return \Generated\Shared\Transfer\NodeTransfer
     */
    protected function mapCategoryNodeDataToNodeTransferWithCategoryTemplates(array $categoryNodeData, NodeTransfer $nodeTransfer): NodeTransfer
    {
        $nodeTransfer = $nodeTransfer->fromArray($categoryNodeData, true);

        if (isset($categoryNodeData[static::CATEGORY])) {
            $categoryData = $categoryNodeData[static::CATEGORY];
            $categoryTransfer = (new CategoryTransfer())->fromArray($categoryData, true);
            $categoryTransfer->setCategoryTemplate($this->getCategoryTemplateFromCache($categoryNodeData[static::CATEGORY][static::COL_FK_CATEGORY_TEMPLATE]));

            $nodeTransfer->setCategory($categoryTransfer);
        }

        return $nodeTransfer;
    }

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\Category\Persistence\SpyCategory> $categoryEntities
     * @param \Generated\Shared\Transfer\CategoryCollectionTransfer $categoryCollectionTransfer
     * @param \Generated\Shared\Transfer\CategoryCriteriaTransfer|null $categoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionTransfer
     */
    public function mapCategoryCollection(
        Collection $categoryEntities,
        CategoryCollectionTransfer $categoryCollectionTransfer,
        ?CategoryCriteriaTransfer $categoryCriteriaTransfer = null
    ): CategoryCollectionTransfer {
        /** @var \Orm\Zed\Category\Persistence\SpyCategory $categoryEntity */
        foreach ($categoryEntities as $categoryEntity) {
            $categoryTransfer = $this->mapCategory($categoryEntity, new CategoryTransfer());
            $categoryTransfer = $this->mapLocalizedAttributes($categoryEntity->getAttributes(), $categoryTransfer);

            foreach ($categoryTransfer->getLocalizedAttributes() as $localizedAttribute) {
                $categoryTransfer->fromArray($localizedAttribute->toArray(), true);
            }

            $nodeCollectionTransfer = $this->categoryNodeMapper->mapNodeCollection(
                $categoryEntity->getNodes(),
                new NodeCollectionTransfer(),
            );
            $categoryTransfer->setNodeCollection($nodeCollectionTransfer);

            $storeRelationTransfer = $this->categoryStoreRelationMapper->mapCategoryStoreEntitiesToStoreRelationTransfer(
                $categoryEntity->getSpyCategoryStores(),
                (new StoreRelationTransfer())->setIdEntity($categoryEntity->getIdCategory()),
            );
            $categoryTransfer->setStoreRelation($storeRelationTransfer);

            if ($categoryCriteriaTransfer) {
                $categoryTransfer = $this->mapCategoryRelations(
                    $categoryCriteriaTransfer,
                    $categoryEntity,
                    $categoryTransfer,
                );
            }

            $categoryCollectionTransfer->addCategory($categoryTransfer);
        }

        return $categoryCollectionTransfer;
    }

    public function mapCategoryRelations(
        CategoryCriteriaTransfer $categoryCriteriaTransfer,
        SpyCategory $categoryEntity,
        CategoryTransfer $categoryTransfer
    ): CategoryTransfer {
        $categoryTransfer = $this->categoryNodeMapper->mapCategoryNodes($categoryEntity, $categoryTransfer);

        $categoryConditions = $categoryCriteriaTransfer->getCategoryConditions();
        if ($categoryConditions !== null) {
            if ($categoryConditions->getWithParentCategory()) {
                $categoryTransfer = $this->mapCategoryParent($categoryEntity, $categoryTransfer);
            }
        }

        return $categoryTransfer;
    }

    protected function mapCategoryParent(SpyCategory $categoryEntity, CategoryTransfer $categoryTransfer): CategoryTransfer
    {
        $categoryTransfer = $this->mapParentCategoryNodes($categoryEntity, $categoryTransfer);

        foreach ($categoryEntity->getNodes() as $categoryNodeEntity) {
            if (!$categoryNodeEntity->isMain()) {
                continue;
            }

            $parentCategoryNode = $categoryNodeEntity->getParentCategoryNode();
            if ($parentCategoryNode) {
                $parentCategory = $parentCategoryNode->getCategory();
                $parentCategoryTransfer = $this->mapCategory($parentCategory, new CategoryTransfer());
                $categoryTransfer->getParentCategoryNodeOrFail()->setCategory($parentCategoryTransfer);
            }

            break;
        }

        return $categoryTransfer;
    }

    public function mapCategoryTransferToCategoryEntity(CategoryTransfer $categoryTransfer, SpyCategory $categoryEntity): SpyCategory
    {
        $categoryEntity->fromArray($categoryTransfer->modifiedToArray());

        return $categoryEntity;
    }

    protected function mapParentCategoryNodes(SpyCategory $categoryEntity, CategoryTransfer $categoryTransfer): CategoryTransfer
    {
        foreach ($categoryEntity->getNodes() as $categoryNodeEntity) {
            $parentCategoryNodeEntity = $categoryNodeEntity->getParentCategoryNode();

            if ($parentCategoryNodeEntity === null) {
                continue;
            }

            if ($categoryNodeEntity->isMain()) {
                $categoryTransfer->setParentCategoryNode($this->categoryNodeMapper->mapCategoryNode($parentCategoryNodeEntity, new NodeTransfer()));

                continue;
            }

            $categoryTransfer->addExtraParent($this->categoryNodeMapper->mapCategoryNode($parentCategoryNodeEntity, new NodeTransfer()));
        }

        return $categoryTransfer;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Category\Persistence\SpyCategoryAttribute> $attributeCollection
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Url\Persistence\SpyUrl>|null $urlEntities
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    protected function mapLocalizedAttributes(
        ObjectCollection $attributeCollection,
        CategoryTransfer $categoryTransfer,
        ?ObjectCollection $urlEntities = null
    ): CategoryTransfer {
        foreach ($attributeCollection as $attribute) {
            $localeTransfer = new LocaleTransfer();
            $localeTransfer->fromArray($attribute->getLocale()->toArray(), true);

            $categoryLocalizedAttributesTransfer = new CategoryLocalizedAttributesTransfer();
            $categoryLocalizedAttributesTransfer->fromArray($attribute->toArray(), true);
            $categoryLocalizedAttributesTransfer->setLocale($localeTransfer);

            if ($urlEntities) {
                $categoryLocalizedAttributesTransfer = $this->categoryLocalizedAttributesUrlMapper->mapUrlEntitiesToCategoryLocalizedAttributesTransfer(
                    $urlEntities,
                    $categoryLocalizedAttributesTransfer,
                );
            }

            $categoryTransfer->addLocalizedAttributes($categoryLocalizedAttributesTransfer);
        }

        return $categoryTransfer;
    }

    protected function getCategoryTemplateFromCache(int $categoryTemplateId): CategoryTemplateTransfer
    {
        if (!isset(static::$categoryTemplateCache[$categoryTemplateId])) {
            $categoryTemplateCollection = $this->categoryTemplateQuery->find();
            foreach ($categoryTemplateCollection as $categoryTemplateEntity) {
                static::$categoryTemplateCache[$categoryTemplateEntity->getIdCategoryTemplate()] = $this->categoryTemplateMapper->mapCategoryTemplateEntityToCategoryTemplateTransfer(
                    $categoryTemplateEntity,
                    new CategoryTemplateTransfer(),
                );
            }
        }

        return static::$categoryTemplateCache[$categoryTemplateId];
    }
}
