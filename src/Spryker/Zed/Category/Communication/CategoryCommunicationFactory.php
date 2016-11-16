<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Communication;

use Spryker\Zed\Category\CategoryDependencyProvider;
use Spryker\Zed\Category\Communication\Form\CategoryType;
use Spryker\Zed\Category\Communication\Form\DataProvider\CategoryCreateDataProvider;
use Spryker\Zed\Category\Communication\Form\DataProvider\CategoryDeleteDataProvider;
use Spryker\Zed\Category\Communication\Form\DataProvider\CategoryEditDataProvider;
use Spryker\Zed\Category\Communication\Form\DeleteType;
use Spryker\Zed\Category\Communication\Table\CategoryAttributeTable;
use Spryker\Zed\Category\Communication\Table\RootNodeTable;
use Spryker\Zed\Category\Communication\Table\UrlTable;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;

/**
 * @method \Spryker\Zed\Category\Persistence\CategoryQueryContainer getQueryContainer()
 * @method \Spryker\Zed\Category\CategoryConfig getConfig()
 * @method \Spryker\Zed\Category\Business\CategoryFacadeInterface getFacade()
 */
class CategoryCommunicationFactory extends AbstractCommunicationFactory
{

    /**
     * @var string
     */
    protected $currentLocale;

    /**
     * @return \Generated\Shared\Transfer\LocaleTransfer
     */
    public function getCurrentLocale()
    {
        if ($this->currentLocale === null) {
            $this->currentLocale = $this->getLocaleFacade()
                ->getCurrentLocale();
        }

        return $this->currentLocale;
    }

    /**
     * @return \Spryker\Zed\Category\Dependency\Facade\CategoryToLocaleInterface
     */
    protected function getLocaleFacade()
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_LOCALE);
    }

    /**
     * @return \Spryker\Zed\Category\Dependency\Facade\CategoryToLocaleInterface
     */
    public function getLocaleFacade()
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_LOCALE);
    }

    /**
     * @return \Spryker\Zed\Category\Communication\Table\RootNodeTable
     */
    public function createRootNodeTable()
    {
        $categoryQueryContainer = $this->getQueryContainer();
        $locale = $this->getCurrentLocale();

        return new RootNodeTable($categoryQueryContainer, $locale->getIdLocale());
    }

    /**
     * @param int|null $idParentNode
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createCategoryCreateForm($idParentNode)
    {
        $categoryCreateForm = new CategoryType();
        $categoryCreateDataFormProvider = $this->createCategoryCreateFormDataProvider();
        $formFactory = $this->getFormFactory();

        return $formFactory->create(
            $categoryCreateForm,
            $categoryCreateDataFormProvider->getData($idParentNode),
            $categoryCreateDataFormProvider->getOptions()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Communication\Form\DataProvider\CategoryCreateDataProvider
     */
    protected function createCategoryCreateFormDataProvider()
    {
        return new CategoryCreateDataProvider(
            $this->getQueryContainer(),
            $this->getLocaleFacade()
        );
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createCategoryEditForm()
    {
        $categoryCreateForm = new CategoryType();
        $categoryCreateDataFormProvider = $this->createCategoryEditFormDataProvider();
        $formFactory = $this->getFormFactory();

        return $formFactory->create(
            $categoryCreateForm,
            $categoryCreateDataFormProvider->getData(),
            $categoryCreateDataFormProvider->getOptions()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Communication\Form\DataProvider\CategoryEditDataProvider
     */
    protected function createCategoryEditFormDataProvider()
    {
        return new CategoryEditDataProvider(
            $this->getQueryContainer(),
            $this->getFacade(),
            $this->getLocaleFacade()
        );
    }

    /**
     * @param int $idCategory
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createCategoryDeleteForm($idCategory)
    {
        $categoryDeleteForm = new DeleteType();
        $categoryDeleteFormDataProvider = $this->createCategoryDeleteFormDataProvider();
        $formFactory = $this->getFormFactory();

        return $formFactory->create(
            $categoryDeleteForm,
            $categoryDeleteFormDataProvider->getData($idCategory),
            $categoryDeleteFormDataProvider->getOptions()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Communication\Form\DataProvider\CategoryDeleteDataProvider
     */
    protected function createCategoryDeleteFormDataProvider()
    {
        return new CategoryDeleteDataProvider(
            $this->getQueryContainer(),
            $this->getLocaleFacade()
        );
    }

    /**
     * @param int $idCategoryNode
     *
     * @return \Spryker\Zed\Category\Communication\Table\CategoryAttributeTable
     */
    public function createCategoryAttributeTable($idCategoryNode)
    {
        if ($idCategoryNode === null) {
            //@TODO: table initialisation with ajax then this part can be deleted
            $idCategoryNode = $this->getQueryContainer()->queryRootNode()->findOne()->getIdCategoryNode();
        }
        $categoryNode = $this->getQueryContainer()->queryCategoryNodeByNodeId($idCategoryNode)->findOne();
        $categoryQueryContainer = $this->getQueryContainer();
        $categoryAttributesQuery = $categoryQueryContainer->queryAttributeByCategoryIdAndLocale(
            $categoryNode->getFkCategory(),
            $this->getCurrentLocale()->getIdLocale()
        );

        return new CategoryAttributeTable($categoryAttributesQuery);
    }

    /**
     * @param int $idCategoryNode
     *
     * @return \Spryker\Zed\Category\Communication\Table\UrlTable
     */
    public function createUrlTable($idCategoryNode)
    {
        if ($idCategoryNode === null) {
            //@TODO: table initialisation with ajax then this part can be deleted
            $idCategoryNode = $this->getQueryContainer()->queryRootNode()->findOne()->getIdCategoryNode();
        }
        $urlQuery = $this->getQueryContainer()
            ->queryUrlByIdCategoryNode($idCategoryNode);

        return new UrlTable($urlQuery);
    }

    /**
     * @return \Spryker\Zed\Category\Dependency\Plugin\CategoryRelationReadPluginInterface[]
     */
    public function getRelationReadPluginStack()
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::PLUGIN_STACK_RELATION_READ);
    }

}
