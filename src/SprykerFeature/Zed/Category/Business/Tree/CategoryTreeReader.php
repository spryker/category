<?php

/**
 * (c) Spryker Systems GmbH copyright protected.
 */

namespace SprykerFeature\Zed\Category\Business\Tree;

use Generated\Shared\Transfer\LocaleTransfer;
use Propel\Runtime\Collection\ObjectCollection;
use SprykerFeature\Zed\Category\Business\Tree\Formatter\CategoryTreeFormatter;
use SprykerFeature\Zed\Category\Persistence\CategoryQueryContainer;
use Orm\Zed\Category\Persistence\Map\SpyCategoryClosureTableTableMap;
use Orm\Zed\Category\Persistence\SpyCategoryNode;
use SprykerFeature\Zed\ProductCategory\Business\Exception\MissingCategoryException;
use SprykerFeature\Zed\ProductCategory\Business\Exception\MissingCategoryNodeException;

class CategoryTreeReader implements CategoryTreeReaderInterface
{

    const ID = 'id';
    const ID_CATEGORY = 'id_category';
    const ID_PARENT = 'parent';
    const TEXT = 'text';
    const IS_ACTIVE = 'is_active';
    const IS_MAIN = 'is_main';
    const IS_CLICKABLE = 'is_clickable';
    const IS_IN_MENU = 'is_in_menu';

    /**
     * @var CategoryQueryContainer
     */
    protected $queryContainer;

    /**
     * @var CategoryTreeFormatter
     */
    protected $treeFormatter;

    /**
     * @param CategoryQueryContainer $queryContainer
     */
    public function __construct(CategoryQueryContainer $queryContainer, CategoryTreeFormatter $treeFormatter)
    {
        $this->queryContainer = $queryContainer;
        $this->treeFormatter = $treeFormatter;
    }

    /**
     * @param int $idNode
     * @param LocaleTransfer $locale
     *
     * @return SpyCategoryNode[]|ObjectCollection
     */
    public function getChildren($idNode, LocaleTransfer $locale)
    {
        return $this->queryContainer
            ->queryFirstLevelChildrenByIdLocale($idNode, $locale->getIdLocale())
            ->find();
    }

    /**
     * @param int $idNode
     * @param LocaleTransfer $locale
     * @param bool $excludeRootNode
     *
     * @return array
     */
    public function getParents($idNode, LocaleTransfer $locale, $excludeRootNode = true)
    {
        return $this->getGroupedPaths($idNode, $locale, $excludeRootNode, true);
    }

    /**
     * @param int $idNode
     * @param LocaleTransfer $locale
     * @param bool $excludeRootNode
     * @param bool $onlyParents
     *
     * @return array
     */
    public function getPath($idNode, LocaleTransfer $locale, $excludeRootNode = true, $onlyParents = false)
    {
        return $this->queryContainer
            ->queryPath($idNode, $locale->getIdLocale(), $excludeRootNode, $onlyParents)
            ->find();
    }

    /**
     * @param int $idParentNode
     * @param bool $excludeRoot
     *
     * @return array
     */
    public function getPathChildren($idParentNode, $excludeRoot = true)
    {
        return $this->queryContainer
            ->getChildrenPath($idParentNode, $excludeRoot)
            ->find();
    }

    /**
     * @param int $idChildNode
     * @param int $idLocale
     * @param bool $excludeRoot
     *
     * @return array
     */
    public function getPathParents($idChildNode, $idLocale, $excludeRoot = true)
    {
        return $this->queryContainer
            ->getParentPath($idChildNode, $idLocale, $excludeRoot)
            ->find();
    }

    /**
     * @param int $idNode
     * @param LocaleTransfer $locale
     * @param bool $excludeRootNode
     * @param bool $onlyParents
     *
     * @return array
     */
    public function getGroupedPaths($idNode, LocaleTransfer $locale, $excludeRootNode = true, $onlyParents = false)
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
     * @param LocaleTransfer $locale
     * @param bool $excludeRootNode
     * @param bool $onlyParents
     *
     * @TODO Move getGroupedPathIds and getGroupedPaths to another class, duplicated Code!
     *
     * @return array
     */
    public function getGroupedPathIds($idNode, LocaleTransfer $locale, $excludeRootNode = true, $onlyParents = false)
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
            ->count();

        return $childrenCount > 0;
    }

    /**
     * @param string $categoryName
     * @param LocaleTransfer $locale
     *
     * @return bool
     */
    public function hasCategoryNode($categoryName, LocaleTransfer $locale)
    {
        $categoryQuery = $this->queryContainer->queryNodeByCategoryName($categoryName, $locale->getIdLocale());

        return $categoryQuery->count() > 0;
    }

    /**
     * @param string $categoryName
     * @param LocaleTransfer $locale
     *
     * @throws MissingCategoryNodeException
     *
     * @return int
     */
    public function getCategoryNodeIdentifier($categoryName, LocaleTransfer $locale)
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
     * @param string $categoryName
     * @param LocaleTransfer $locale
     *
     * @throws MissingCategoryException
     *
     * @return int
     */
    public function getCategoryIdentifier($categoryName, LocaleTransfer $locale)
    {
        $categoryQuery = $this->queryContainer->queryCategoryAttributesByName($categoryName, $locale->getIdLocale());
        $category = $categoryQuery->findOne();

        if (!$category) {
            throw new MissingCategoryException(
                sprintf(
                    'Tried to retrieve missing attributes of category %s, locale %s',
                    $categoryName,
                    $locale->getLocaleName()
                )
            );
        }

        return $category->getFkCategory();
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
            ->findOne();
    }

    /**
     * @param $idCategory
     * @param $idParentNode
     *
     * @return SpyCategoryNode
     */
    public function getNodeByIdCategoryAndParentNode($idCategory, $idParentNode)
    {
        return $this->queryContainer
            ->queryNodeByIdCategoryAndParentNode($idCategory, $idParentNode)
            ->findOne();
    }

    /**
     * @return SpyCategoryNode[]|ObjectCollection
     */
    public function getRootNodes()
    {
        return $this->queryContainer
            ->queryRootNode()
            ->find();
    }

    /**
     * @param int $idCategory
     *
     * @return SpyCategoryNode[]|ObjectCollection
     */
    public function getAllNodesByIdCategory($idCategory)
    {
        return $this->queryContainer
            ->queryAllNodesByCategoryId($idCategory)
            ->find();
    }

    /**
     * @param int $idCategory
     *
     * @return SpyCategoryNode[]|ObjectCollection
     */
    public function getMainNodesByIdCategory($idCategory)
    {
        return $this->queryContainer
            ->queryMainNodesByCategoryId($idCategory)
            ->find();
    }

    /**
     * @param int $idCategory
     *
     * @return SpyCategoryNode[]|ObjectCollection
     */
    public function getNotMainNodesByIdCategory($idCategory)
    {
        return $this->queryContainer
            ->queryNotMainNodesByCategoryId($idCategory)
            ->find();
    }

    /**
     * @param int $idParentNode
     * @param int $idLocale
     *
     * @return SpyCategoryNode[]|ObjectCollection
     */
    public function getCategoryNodesWithOrder($idParentNode, $idLocale)
    {
        return $this->queryContainer
            ->getCategoryNodesWithOrder($idParentNode, $idLocale)
            ->find();
    }

    /**
     * @param int $idCategory
     * @param LocaleTransfer $localeTransfer
     *
     * @return array
     */
    public function getTree($idCategory, LocaleTransfer $localeTransfer)
    {
        $nodes = $this->getAllNodesByIdCategory($idCategory);
        $categoryNodes = $nodes->getData();
        if ($categoryNodes) {
            return $this->getTreeNodesRecursively($categoryNodes[0], $localeTransfer, true);
        }

        return $this->getTreeNodesRecursively(null, $localeTransfer, true);
    }

    /**
     * @param SpyCategoryNode $node
     * @param LocaleTransfer $localeTransfer
     * @param bool $isRoot
     *
     * @return array
     */
    protected function getTreeNodesRecursively(SpyCategoryNode $node = null, LocaleTransfer $localeTransfer, $isRoot = false)
    {
        $tree = [];
        if ($node === null) {
            $children = $this->getRootNodes();
        } else {
            $children = $this->getChildren($node->getIdCategoryNode(), $localeTransfer);
        }
        if ($isRoot) {
            $idParent = 0;
        } else {
            $idParent = $node->getIdCategoryNode();
        }

        foreach ($children as $child) {
            $text = $child->getCategory()
                ->getLocalisedAttributes($localeTransfer->getIdLocale())
                ->getFirst()
                ->getName();

            $tree[] = [
                self::ID => $child->getIdCategoryNode(),
                self::ID_CATEGORY => $child->getFkCategory(),
                self::ID_PARENT => $idParent,
                self::TEXT => $text,
                self::IS_MAIN => $child->getIsMain(),
                self::IS_ACTIVE => $child->getCategory()->isActive(),
                self::IS_IN_MENU => $child->getCategory()->getIsInMenu(),
                self::IS_CLICKABLE => $child->getCategory()->getIsClickable(),
            ];
            if ($child->countDescendants() > 0) {
                $tree = array_merge($tree, $this->getTreeNodesRecursively($child, $localeTransfer));
            }
        }

        return $tree;
    }

    /**
     * @param int $idCategory
     * @param LocaleTransfer $locale
     *
     * @return array
     */
    public function getTreeNodeChildren($idCategory, LocaleTransfer $locale)
    {
        $categories = $this->getTree(
            $idCategory,
            $locale
        );

        return $categories;
    }

    /**
     * @param int $idCategory
     * @param LocaleTransfer $locale
     *
     * @return array
     */
    public function getTreeNodeChildrenByIdCategoryAndLocale($idCategory, LocaleTransfer $locale)
    {
        $categories = $this->getTreeNodeChildren($idCategory, $locale);

        $this->treeFormatter->setupCategories($categories);

        return $this->treeFormatter->getCategoryTree();
    }

}
