<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Category\Helper;

use Codeception\Module;
use Codeception\TestInterface;
use Generated\Shared\Transfer\CategoryLocalizedAttributesTransfer;
use Generated\Shared\Transfer\CategoryTemplateTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Orm\Zed\Category\Persistence\SpyCategory;
use Orm\Zed\Category\Persistence\SpyCategoryQuery;
use Silex\Application;
use Spryker\Service\Container\Container;
use Spryker\Zed\Category\Business\CategoryFacade;
use Spryker\Zed\Category\CategoryConfig;
use Spryker\Zed\Locale\Business\LocaleFacade;
use Spryker\Zed\Propel\Communication\Plugin\Application\PropelApplicationPlugin;
use Spryker\Zed\Propel\Communication\Plugin\ServiceProvider\PropelServiceProvider;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;
use SprykerTest\Zed\Category\PageObject\CategoryCreatePage;

class CategoryHelper extends Module
{
    use LocatorHelperTrait;

    /**
     * @return void
     */
    public function _initialize(): void
    {
        if (class_exists(PropelApplicationPlugin::class)) {
            $propelApplicationPlugin = new PropelApplicationPlugin();
            $propelApplicationPlugin->provide(new Container());

            return;
        }

        $this->addBackwardCompatibleServiceProvider();
    }

    /**
     * @param \Codeception\TestInterface $test
     *
     * @return void
     */
    public function _before(TestInterface $test): void
    {
        parent::_before($test);

        $this->cleanUpDatabase();
    }

    /**
     * @return void
     */
    public function _afterSuite(): void
    {
        parent::_afterSuite();

        $this->cleanUpDatabase();
    }

    /**
     * @param \Codeception\TestInterface $test
     * @param \Exception $fail
     *
     * @return void
     */
    public function _failed(TestInterface $test, $fail): void
    {
        parent::_failed($test, $fail);

        $this->cleanUpDatabase();
    }

    /**
     * @param string $categoryKey
     *
     * @return \Generated\Shared\Transfer\CategoryTransfer
     */
    public function createCategory(string $categoryKey): CategoryTransfer
    {
        $categoryFacade = new CategoryFacade();
        $categoryTemplateTransfer = $this->findCategoryTemplateByName(CategoryConfig::CATEGORY_TEMPLATE_DEFAULT);

        $categoryTransfer = (new CategoryTransfer())
            ->setCategoryKey($categoryKey)
            ->setFkCategoryTemplate($categoryTemplateTransfer->getIdCategoryTemplate())
            ->setIsActive(false);

        $this->addLocalizedAttributesToCategoryTransfer($categoryTransfer);

        $categoryNodeTransfer = new NodeTransfer();
        $categoryNodeTransfer->setFkCategory($categoryTransfer->getIdCategory());
        $categoryNodeTransfer->setIsMain(false);
        $categoryTransfer->setCategoryNode($categoryNodeTransfer);

        $parentCategoryNodeTransfer = new NodeTransfer();
        $parentCategoryNodeTransfer->setIdCategoryNode(1);
        $categoryTransfer->setParentCategoryNode($parentCategoryNodeTransfer);

        $categoryFacade->create($categoryTransfer);

        return $categoryTransfer;
    }

    /**
     * @param string $categoryKey
     *
     * @return \Orm\Zed\Category\Persistence\SpyCategory
     */
    public function loadCategoryByCategoryKey(string $categoryKey): SpyCategory
    {
        $categoryQuery = new SpyCategoryQuery();

        return $categoryQuery->findOneByCategoryKey($categoryKey);
    }

    /**
     * @deprecated Will be removed in favor of {@link \Spryker\Zed\Propel\Communication\Plugin\Application\PropelApplicationPlugin}.
     *
     * @return void
     */
    protected function addBackwardCompatibleServiceProvider(): void
    {
        $propelServiceProvider = new PropelServiceProvider();
        $propelServiceProvider->boot(new Application());
    }

    /**
     * @param string $categoryKey
     *
     * @return void
     */
    protected function removeCategory(string $categoryKey): void
    {
        $categoryQuery = new SpyCategoryQuery();
        $categoryEntity = $categoryQuery->findOneByCategoryKey($categoryKey);
        if (!$categoryEntity) {
            return;
        }
        $attributeEntityCollection = $categoryEntity->getAttributes();
        if ($attributeEntityCollection) {
            $attributeEntityCollection->delete();
        }

        $nodeEntityCollection = $categoryEntity->getNodes();
        if ($nodeEntityCollection) {
            foreach ($nodeEntityCollection as $nodeEntity) {
                $closureTableEntries = $nodeEntity->getDescendants();
                if ($closureTableEntries) {
                    $closureTableEntries->delete();
                }
            }
            $nodeEntityCollection->delete();
        }

        $categoryEntity->delete();
    }

    /**
     * @param string $name
     *
     * @return \Generated\Shared\Transfer\CategoryTemplateTransfer|null
     */
    protected function findCategoryTemplateByName(string $name): ?CategoryTemplateTransfer
    {
        $spyCategoryTemplate = $this->getLocator()
            ->category()
            ->queryContainer()
            ->queryCategoryTemplateByName($name)
            ->findOne();

        if (!$spyCategoryTemplate) {
            return null;
        }

        return (new CategoryTemplateTransfer())->fromArray($spyCategoryTemplate->toArray(), true);
    }

    /**
     * @param \Generated\Shared\Transfer\CategoryTransfer $categoryTransfer
     *
     * @return void
     */
    protected function addLocalizedAttributesToCategoryTransfer(CategoryTransfer $categoryTransfer): void
    {
        $localeTransferCollection = $this->getLocaleTransferCollection();

        foreach ($localeTransferCollection as $localeTransfer) {
            $categoryLocalizedAttributesTransfer = new CategoryLocalizedAttributesTransfer();
            $categoryLocalizedAttributesTransfer->setLocale($localeTransfer);
            $categoryLocalizedAttributesTransfer->setName(
                $categoryTransfer->getCategoryKey() . ' name ' . $localeTransfer->getLocaleName(),
            );
            $categoryLocalizedAttributesTransfer->setMetaTitle(
                $categoryTransfer->getCategoryKey() . ' title ' . $localeTransfer->getLocaleName(),
            );
            $categoryLocalizedAttributesTransfer->setMetaDescription(
                $categoryTransfer->getCategoryKey() . ' description ' . $localeTransfer->getLocaleName(),
            );
            $categoryTransfer->addLocalizedAttributes($categoryLocalizedAttributesTransfer);
        }
    }

    /**
     * @return array<\Generated\Shared\Transfer\LocaleTransfer>
     */
    protected function getLocaleTransferCollection(): array
    {
        $localeFacade = new LocaleFacade();
        $localeTransferCollection = $localeFacade->getLocaleCollection();

        return $localeTransferCollection;
    }

    /**
     * @return void
     */
    private function cleanUpDatabase(): void
    {
        $this->removeCategory(CategoryCreatePage::CATEGORY_A);
        $this->removeCategory(CategoryCreatePage::CATEGORY_B);
    }
}
