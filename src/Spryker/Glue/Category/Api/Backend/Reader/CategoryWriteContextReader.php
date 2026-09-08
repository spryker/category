<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Reader;

use ArrayObject;
use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryConditionsTransfer;
use Generated\Shared\Transfer\CategoryCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTemplateCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTemplateTransfer;
use Generated\Shared\Transfer\CategoryWriteContextTransfer;
use Spryker\Zed\Category\Business\CategoryFacadeInterface;
use Spryker\Zed\Locale\Business\LocaleFacadeInterface;
use Spryker\Zed\Store\Business\StoreFacadeInterface;

class CategoryWriteContextReader implements CategoryWriteContextReaderInterface
{
    public function __construct(
        protected CategoryFacadeInterface $categoryFacade,
        protected StoreFacadeInterface $storeFacade,
        protected LocaleFacadeInterface $localeFacade,
    ) {
    }

    public function getCategoryWriteContext(CategoriesBackendResource $categoriesBackendResource): CategoryWriteContextTransfer
    {
        $categoryWriteContextTransfer = (new CategoryWriteContextTransfer())
            ->setCategories($this->getCategories($this->collectCategoryKeys($categoriesBackendResource)))
            ->setStores(new ArrayObject($this->storeFacade->getStoreTransfersByStoreNames(
                array_values(array_unique($categoriesBackendResource->stores ?? [])),
            )))
            ->setLocales(new ArrayObject(array_values($this->localeFacade->getLocaleCollection())));

        $categoryTemplateTransfers = $this->categoryFacade
            ->getCategoryTemplateCollection(new CategoryTemplateCriteriaTransfer())
            ->getCategoryTemplates();

        return $categoryWriteContextTransfer
            ->setCategoryTemplates($categoryTemplateTransfers)
            ->setCategoryTemplate($this->findCategoryTemplate($categoryTemplateTransfers, $categoriesBackendResource->templateName));
    }

    /**
     * @return array<string>
     */
    protected function collectCategoryKeys(CategoriesBackendResource $categoriesBackendResource): array
    {
        $categoryKeys = $categoriesBackendResource->extraParentCategoryKeys ?? [];

        if ($categoriesBackendResource->parentCategoryKey !== null) {
            $categoryKeys[] = $categoriesBackendResource->parentCategoryKey;
        }

        if ($categoriesBackendResource->categoryKey !== null) {
            $categoryKeys[] = $categoriesBackendResource->categoryKey;
        }

        return array_values(array_unique($categoryKeys));
    }

    /**
     * @param array<string> $categoryKeys
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\CategoryTransfer>
     */
    protected function getCategories(array $categoryKeys): ArrayObject
    {
        if ($categoryKeys === []) {
            return new ArrayObject();
        }

        return $this->categoryFacade->getCategoryCollection(
            (new CategoryCriteriaTransfer())->setCategoryConditions(
                (new CategoryConditionsTransfer())->setCategoryKeys($categoryKeys),
            ),
        )->getCategories();
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\CategoryTemplateTransfer> $categoryTemplateTransfers
     */
    protected function findCategoryTemplate(ArrayObject $categoryTemplateTransfers, ?string $templateName): ?CategoryTemplateTransfer
    {
        if ($templateName === null) {
            return null;
        }

        $templateName = trim($templateName);
        foreach ($categoryTemplateTransfers as $categoryTemplateTransfer) {
            if ($categoryTemplateTransfer->getName() === $templateName) {
                return $categoryTemplateTransfer;
            }
        }

        return null;
    }
}
