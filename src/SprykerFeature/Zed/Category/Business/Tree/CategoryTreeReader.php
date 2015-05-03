<?php

namespace SprykerFeature\Zed\Category\Business\Tree;

use Propel\Runtime\Collection\ObjectCollection;
use SprykerEngine\Shared\Locale\Dto\LocaleDto;
use SprykerFeature\Zed\Category\Persistence\CategoryQueryContainer;
use SprykerFeature\Zed\Category\Persistence\Propel\Map\SpyCategoryClosureTableTableMap;
use SprykerFeature\Zed\Category\Persistence\Propel\SpyCategoryNode;
use SprykerFeature\Zed\ProductCategory\Business\Exception\MissingCategoryNodeException;

class CategoryTreeReader implements CategoryTreeReaderInterface
{
    /**
     * @var CategoryQueryContainer
     */
    protected $queryContainer;

    /**
     * @param CategoryQueryContainer $queryContainer
     */
    public function __construct(CategoryQueryContainer $queryContainer)
    {
        $this->queryContainer = $queryContainer;
    }

    /**
     * @param int $idNode
     * @param LocaleDto $locale
     * @param bool $onlyOneLevel
     * @param bool $excludeStartNode
     *
     * @return SpyCategoryNode[]|ObjectCollection
     */
    public function getChildren($idNode, LocaleDto $locale, $onlyOneLevel = true, $excludeStartNode = true)
    {
        return $this->queryContainer
            ->queryChildren($idNode, $locale->getIdLocale(), $onlyOneLevel, $excludeStartNode)
            ->find()
            ;
    }

    /**
     * @param int $idNode
     * @param LocaleDto $locale
     * @param bool $excludeRootNode
     *
     * @return array
     */
    public function getParents($idNode, LocaleDto $locale, $excludeRootNode = true)
    {
        return $this->getGroupedPaths($idNode, $locale, $excludeRootNode, true);
    }

    /**
     * @param int $idNode
     * @param LocaleDto $locale
     * @param bool $excludeRootNode
     * @param bool $onlyParents
     *
     * @return array
     */
    public function getPath($idNode, LocaleDto $locale, $excludeRootNode = true, $onlyParents = false)
    {
        return $this->queryContainer
            ->queryPath($idNode, $locale->getIdLocale(), $excludeRootNode, $onlyParents)
            ->find()
            ;
    }

    /**
     * @param int $idNode
     * @param LocaleDto $locale
     * @param bool $excludeRootNode
     * @param bool $onlyParents
     *
     * @return array
     */
    public function getGroupedPaths($idNode, LocaleDto $locale, $excludeRootNode = true, $onlyParents = false)
    {
        $paths = $this->getPath($idNode, $locale, $excludeRootNode, $onlyParents);
        $groupedPaths = [];

        $field = $this->getNodeDescendantColumnName();

        foreach ($paths as $path) {
            $currentId = $path[$field];

            if (!isset($groupedPaths[$currentId])) {
                $groupedPaths[$currentId] = [];
            }
            $groupedPaths[$currentId][] = $path;
        }

        return $groupedPaths;
    }

    /**
     * @param int $idNode
     * @param LocaleDto $locale
     * @param bool $excludeRootNode
     * @param bool $onlyParents
     * @TODO Move getGroupedPathIds and getGroupedPaths to another class, duplicated Code!
     * @return array
     */
    public function getGroupedPathIds($idNode, LocaleDto $locale, $excludeRootNode = true, $onlyParents = false)
    {
        $paths = $this->getPath($idNode, $locale, $excludeRootNode, $onlyParents);

        $groupedPathIds = [];
        $field = $this->getNodeDescendantColumnName();

        foreach ($paths as $path) {
            $idCurrent = $path[$field];

            if (!isset($groupedPathIds[$idCurrent])) {
                $groupedPathIds[$idCurrent] = [];
            }
            $groupedPathIds[$idCurrent][] = $path['id_category_node'];
        }

        return $groupedPathIds;
    }

    /**
     * @return string
     */
    protected function getNodeDescendantColumnName()
    {
        $prefixedColumnName = SpyCategoryClosureTableTableMap::COL_FK_CATEGORY_NODE_DESCENDANT;
        $fieldNameStartPosition = strpos($prefixedColumnName, '.') + 1;
        $columnNameLength = strlen($prefixedColumnName) - $fieldNameStartPosition;
        $columnName = substr($prefixedColumnName, $fieldNameStartPosition, $columnNameLength);

        return $columnName;
    }

    /**
     * @param int $idNode
     *
     * @return bool
     */
    public function hasChildren($idNode)
    {
        $childrenCount = $this->queryContainer
            ->queryFirstLevelChildren($idNode)
            ->count()
        ;

        return  $childrenCount > 0;
    }

    /**
     * @param string $categoryName
     * @param LocaleDto $locale
     *
     * @return bool
     */
    public function hasCategoryNode($categoryName, LocaleDto $locale)
    {
        $categoryQuery = $this->queryContainer->queryNodeByCategoryName($categoryName, $locale->getIdLocale());

        return $categoryQuery->count() > 0;
    }

    /**
     * @param string $categoryName
     * @param LocaleDto $locale
     *
     * @return int
     * @throws MissingCategoryNodeException
     */
    public function getCategoryNodeIdentifier($categoryName, LocaleDto $locale)
    {
        $categoryQuery = $this->queryContainer->queryNodeByCategoryName($categoryName, $locale->getIdLocale());
        $categoryNode = $categoryQuery->findOne();

        if (!$categoryNode) {
            throw new MissingCategoryNodeException(
                sprintf(
                    'Tried to retrieve a missing category node for category %s, locale %s',
                    $categoryName,
                    $locale->getLocaleName()
                )
            );
        }

        return $categoryNode->getPrimaryKey();
    }

    /**
     * @param int $idNode
     *
     * @return SpyCategoryNode
     */
    public function getNodeById($idNode)
    {
        return $this->queryContainer
            ->queryNodeById($idNode)
            ->findOne()
            ;
    }

    /**
     * @param int $idCategory
     *
     * @return SpyCategoryNode[]
     */
    public function getNodesByIdCategory($idCategory)
    {
        return $this->queryContainer
            ->queryNodesByCategoryId($idCategory)
            ->find()
            ;
    }
}
