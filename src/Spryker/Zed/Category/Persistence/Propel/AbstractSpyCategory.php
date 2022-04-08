<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Persistence\Propel;

use Orm\Zed\Category\Persistence\Base\SpyCategory as BaseSpyCategory;
use Orm\Zed\Category\Persistence\Map\SpyCategoryAttributeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Skeleton subclass for representing a row from the 'spy_category' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements. This class will only be generated as
 * long as it does not already exist in the output directory.
 */
abstract class AbstractSpyCategory extends BaseSpyCategory
{
    /**
     * @param int $idLocale
     *
     * @return \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Category\Persistence\SpyCategoryAttribute>
     */
    public function getLocalisedAttributes($idLocale)
    {
        $criteria = new Criteria();
        $criteria->addAnd(
            SpyCategoryAttributeTableMap::COL_FK_LOCALE,
            $idLocale,
            Criteria::EQUAL,
        );

        return $this->getAttributes($criteria);
    }
}
