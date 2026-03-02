<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeCollectionTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Orm\Zed\Category\Persistence\SpyCategory;
use Orm\Zed\Category\Persistence\SpyCategoryNode;
use Propel\Runtime\Collection\ObjectCollection;

class CategoryNodeMapper
{
    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Category\Persistence\SpyCategoryNode> $nodeEntities
     * @param \Generated\Shared\Transfer\NodeCollectionTransfer $nodeCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\NodeCollectionTransfer
     */
    public function mapNodeCollection(ObjectCollection $nodeEntities, NodeCollectionTransfer $nodeCollectionTransfer): NodeCollectionTransfer
    {
        foreach ($nodeEntities as $nodeEntity) {
            $nodeCollectionTransfer->addNode($this->mapCategoryNode($nodeEntity, new NodeTransfer()));
        }

        return $nodeCollectionTransfer;
    }

    public function mapNodeTransferToCategoryNodeEntity(NodeTransfer $nodeTransfer, SpyCategoryNode $categoryNodeEntity): SpyCategoryNode
    {
        $categoryNodeEntity->fromArray($nodeTransfer->modifiedToArray());

        return $categoryNodeEntity;
    }

    public function mapCategoryNode(SpyCategoryNode $spyCategoryNode, NodeTransfer $nodeTransfer): NodeTransfer
    {
        return $nodeTransfer->fromArray($spyCategoryNode->toArray(), true);
    }

    public function mapCategoryNodes(SpyCategory $categoryEntity, CategoryTransfer $categoryTransfer): CategoryTransfer
    {
        foreach ($categoryEntity->getNodes() as $categoryNodeEntity) {
            if (!$categoryNodeEntity->isMain()) {
                continue;
            }
            $nodeTransfer = $this->mapCategoryNode($categoryNodeEntity, new NodeTransfer());
            $nodeTransfer->setCategory(clone $categoryTransfer);
            $categoryTransfer->setCategoryNode($nodeTransfer);
        }

        return $categoryTransfer;
    }
}
