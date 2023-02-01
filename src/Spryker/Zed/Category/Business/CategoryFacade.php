<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business;

use Generated\Shared\Transfer\CategoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\CategoryCollectionRequestTransfer;
use Generated\Shared\Transfer\CategoryCollectionResponseTransfer;
use Generated\Shared\Transfer\CategoryCollectionTransfer;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\CategoryNodeCriteriaTransfer;
use Generated\Shared\Transfer\CategoryNodeUrlCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\NodeCollectionTransfer;
use Generated\Shared\Transfer\UpdateCategoryStoreRelationRequestTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\Category\Business\CategoryBusinessFactory getFactory()
 * @method \Spryker\Zed\Category\Persistence\CategoryRepositoryInterface getRepository()
 * @method \Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface getEntityManager()
 */
class CategoryFacade extends AbstractFacade implements CategoryFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCategory
     *
     * @return array<\Generated\Shared\Transfer\NodeTransfer>
     */
    public function getAllNodesByIdCategory(int $idCategory): array
    {
        return $this->getFactory()
            ->createCategoryNodeReader()
            ->getAllNodesByIdCategory($idCategory);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @throws \Spryker\Zed\Category\Business\Exception\CategoryUrlExistsException
     *
     * @return void
     */
    public function create(CategoryTransfer $categoryTransfer): void
    {
        $this
            ->getFactory()
            ->createCategoryCreator()
            ->createCategory($categoryTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    public function update(CategoryTransfer $categoryTransfer): void
    {
        $this
            ->getFactory()
            ->createCategoryUpdater()
            ->updateCategory($categoryTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\UpdateCategoryStoreRelationRequestTransfer $updateCategoryStoreRelationRequestTransfer
     *
     * @return void
     */
    public function updateCategoryStoreRelation(
        UpdateCategoryStoreRelationRequestTransfer $updateCategoryStoreRelationRequestTransfer
    ): void {
        $this->getFactory()
            ->createCategoryStoreRelationUpdater()
            ->updateCategoryStoreRelation($updateCategoryStoreRelationRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\UpdateCategoryStoreRelationRequestTransfer $updateCategoryStoreRelationRequestTransfer
     *
     * @return void
     */
    public function updateCategoryStoreRelationWithMainChildrenPropagation(
        UpdateCategoryStoreRelationRequestTransfer $updateCategoryStoreRelationRequestTransfer
    ): void {
        $this->getFactory()
            ->createCategoryStoreUpdater()
            ->updateCategoryStoreRelationWithMainChildrenPropagation($updateCategoryStoreRelationRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCategory
     *
     * @return void
     */
    public function delete(int $idCategory): void
    {
        $this
            ->getFactory()
            ->createCategoryDeleter()
            ->deleteCategory($idCategory);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCategoryNode
     * @param int $position
     *
     * @return void
     */
    public function updateCategoryNodeOrder(int $idCategoryNode, int $position): void
    {
        $this->getFactory()
            ->createCategoryNodeUpdater()
            ->updateCategoryNodeOrder($idCategoryNode, $position);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCategory
     *
     * @return void
     */
    public function touchCategoryActive(int $idCategory): void
    {
        $this
            ->getFactory()
            ->createCategoryToucher()
            ->touchCategoryActive($idCategory);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return bool
     */
    public function checkSameLevelCategoryByNameExists(string $name, CategoryTransfer $categoryTransfer): bool
    {
        return $this->getRepository()->checkSameLevelCategoryByNameExists($name, $categoryTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionTransfer
     */
    public function getAllCategoryCollection(LocaleTransfer $localeTransfer): CategoryCollectionTransfer
    {
        return $this->getFactory()
            ->createCategoryReader()
            ->getAllCategoryCollection($localeTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @deprecated Use {@link \Spryker\Zed\Category\Business\CategoryFacade::getCategoryCollection()} instead.
     *
     * @param \Generated\Shared\Transfer\CategoryCriteriaTransfer $categoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionTransfer
     */
    public function getCategoriesByCriteria(CategoryCriteriaTransfer $categoryCriteriaTransfer): CategoryCollectionTransfer
    {
        return $this->getRepository()->getCategoriesByCriteria($categoryCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCategory
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer|null
     */
    public function findCategoryById(int $idCategory): ?CategoryTransfer
    {
        return $this
            ->getFactory()
            ->createCategoryReader()
            ->findCategoryById($idCategory);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idNode
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return string
     */
    public function getNodePath(int $idNode, LocaleTransfer $localeTransfer): string
    {
        return $this->getRepository()->getCategoryNodePath($idNode, $localeTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getCategoryListUrl(): string
    {
        return $this->getFactory()->getConfig()->getDefaultRedirectUrl();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryCriteriaTransfer $categoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer|null
     */
    public function findCategory(CategoryCriteriaTransfer $categoryCriteriaTransfer): ?CategoryTransfer
    {
        return $this->getFactory()
            ->createCategoryReader()
            ->findCategory($categoryCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\NodeCollectionTransfer
     */
    public function getCategoryNodesWithRelativeNodes(
        CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer
    ): NodeCollectionTransfer {
        return $this->getFactory()
            ->createCategoryNodeReader()
            ->getCategoryNodesWithRelativeNodes($categoryNodeCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\NodeCollectionTransfer
     */
    public function getCategoryNodes(CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer): NodeCollectionTransfer
    {
        return $this->getRepository()->getCategoryNodes($categoryNodeCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryNodeUrlCriteriaTransfer $categoryNodeUrlCriteriaTransfer
     *
     * @return array<\Generated\Shared\Transfer\UrlTransfer>
     */
    public function getCategoryNodeUrls(CategoryNodeUrlCriteriaTransfer $categoryNodeUrlCriteriaTransfer): array
    {
        return $this->getRepository()->getCategoryNodeUrls($categoryNodeUrlCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryCriteriaTransfer $categoryCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionTransfer
     */
    public function getCategoryCollection(CategoryCriteriaTransfer $categoryCriteriaTransfer): CategoryCollectionTransfer
    {
        return $this->getRepository()->getCategoryCollection($categoryCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer
     *
     * @return array<int, array<string>>
     */
    public function getAscendantCategoryKeysGroupedByIdCategoryNode(CategoryNodeCriteriaTransfer $categoryNodeCriteriaTransfer): array
    {
        return $this->getFactory()
            ->createCategoryReader()
            ->getAscendantCategoryKeysGroupedByIdCategoryNode($categoryNodeCriteriaTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryCollectionRequestTransfer $categoryCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionResponseTransfer
     */
    public function createCategoryCollection(
        CategoryCollectionRequestTransfer $categoryCollectionRequestTransfer
    ): CategoryCollectionResponseTransfer {
        return $this->getFactory()->createCategoryCreator()->createCategoryCollection($categoryCollectionRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryCollectionRequestTransfer $categoryCollectionRequestTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionResponseTransfer
     */
    public function updateCategoryCollection(
        CategoryCollectionRequestTransfer $categoryCollectionRequestTransfer
    ): CategoryCollectionResponseTransfer {
        return $this->getFactory()->createCategoryUpdater()->updateCategoryCollection($categoryCollectionRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CategoryCollectionDeleteCriteriaTransfer $categoryCollectionDeleteCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\CategoryCollectionResponseTransfer
     */
    public function deleteCategoryCollection(
        CategoryCollectionDeleteCriteriaTransfer $categoryCollectionDeleteCriteriaTransfer
    ): CategoryCollectionResponseTransfer {
        return $this->getFactory()->createCategoryDeleter()->deleteCategoryCollection($categoryCollectionDeleteCriteriaTransfer);
    }
}
