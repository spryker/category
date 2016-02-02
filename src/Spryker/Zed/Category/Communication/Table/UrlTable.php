<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Category\Communication\Table;

use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;
use Orm\Zed\Url\Persistence\Map\SpyUrlTableMap;
use Orm\Zed\Url\Persistence\SpyUrlQuery;

class UrlTable extends AbstractTable
{

    const TABLE_IDENTIFIER = 'url_table';

    /**
     * @param \Orm\Zed\Url\Persistence\SpyUrlQuery $urlQuery
     */
    public function __construct(SpyUrlQuery $urlQuery)
    {
        $this->urlQuery = $urlQuery;
        $this->defaultUrl = 'urlTable';
        $this->setTableIdentifier(self::TABLE_IDENTIFIER);
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return \Spryker\Zed\Gui\Communication\Table\TableConfiguration
     */
    protected function configure(TableConfiguration $config)
    {
        $config->setHeader([
            SpyUrlTableMap::COL_FK_RESOURCE_CATEGORYNODE => 'Category node Id',
            SpyUrlTableMap::COL_FK_LOCALE => 'Fk Locale',
            SpyUrlTableMap::COL_URL => 'Url',
            SpyUrlTableMap::COL_FK_RESOURCE_REDIRECT => 'Fk Resource Redirect',
        ]);
        $config->setSortable([
            SpyUrlTableMap::COL_URL,
        ]);

        return $config;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return array
     */
    protected function prepareData(TableConfiguration $config)
    {
        $query = $this->urlQuery;
        $queryResults = $this->runQuery($query, $config);
        $results = [];
        foreach ($queryResults as $attribute) {
            $results[] = [
                SpyUrlTableMap::COL_FK_RESOURCE_CATEGORYNODE => $attribute[SpyUrlTableMap::COL_FK_RESOURCE_CATEGORYNODE],
                SpyUrlTableMap::COL_FK_LOCALE => $attribute['spy_localelocale_name'], //@todo: refactor when table alias is fixed (missing .)
                SpyUrlTableMap::COL_URL => $attribute[SpyUrlTableMap::COL_URL],
                SpyUrlTableMap::COL_FK_RESOURCE_REDIRECT => $attribute[SpyUrlTableMap::COL_FK_RESOURCE_REDIRECT],
            ];
        }
        unset($queryResults);

        return $results;
    }

}
