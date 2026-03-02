<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Expander;

use Generated\Shared\Transfer\NodeCollectionTransfer;

class CategoryNodeRelationExpanderComposite implements CategoryNodeRelationExpanderInterface
{
    /**
     * @var array<\Spryker\Zed\Category\Business\Expander\CategoryNodeRelationExpanderInterface>
     */
    protected array $categoryNodeRelationExpanders;

    /**
     * @param array<\Spryker\Zed\Category\Business\Expander\CategoryNodeRelationExpanderInterface> $categoryNodeRelationExpanders
     */
    public function __construct(array $categoryNodeRelationExpanders)
    {
        $this->categoryNodeRelationExpanders = $categoryNodeRelationExpanders;
    }

    public function expandNodeCollectionWithRelations(
        NodeCollectionTransfer $categoryNodeCollectionTransfer
    ): NodeCollectionTransfer {
        foreach ($this->categoryNodeRelationExpanders as $categoryNodeRelationExpander) {
            $categoryNodeCollectionTransfer = $categoryNodeRelationExpander->expandNodeCollectionWithRelations($categoryNodeCollectionTransfer);
        }

        return $categoryNodeCollectionTransfer;
    }
}
