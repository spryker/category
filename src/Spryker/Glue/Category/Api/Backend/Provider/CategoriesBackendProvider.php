<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Provider;

use Generated\Shared\Transfer\SortTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Glue\Category\Api\Backend\Exception\CategoriesExceptionFactory;
use Spryker\Glue\Category\Api\Backend\Reader\CategoryReaderInterface;

class CategoriesBackendProvider extends AbstractBackendProvider
{
    protected const string URI_VARIABLE_CATEGORY_KEY = 'categoryKey';

    protected const string QUERY_PARAM_SORT = 'sort';

    protected const string QUERY_PARAM_PARENT_CATEGORY_KEY = 'parentCategoryKey';

    protected const string SORT_DESCENDING_PREFIX = '-';

    /**
     * @var array<string, string>
     */
    protected const array SORT_FIELD_MAP = [
        'categoryKey' => 'spy_category.category_key',
        'position' => 'node.node_order',
        'name' => 'spy_category_attribute.name',
    ];

    public function __construct(
        protected CategoryReaderInterface $categoryReader,
        protected CategoriesExceptionFactory $exceptionFactory,
    ) {
    }

    protected function provideItem(): ?object
    {
        $categoryKey = (string)$this->getUriVariable(static::URI_VARIABLE_CATEGORY_KEY);

        return $this->categoryReader->findCategoryResourceByCategoryKey($categoryKey);
    }

    /**
     * @return array<\Generated\Api\Backend\CategoriesBackendResource>
     */
    protected function provideCollection(): array
    {
        $paginationTransfer = $this->buildPaginationTransfer();

        $childCategoryIds = $this->resolveChildCategoryIdsFromFilter();
        if ($childCategoryIds === []) {
            $this->setCollectionPagination($paginationTransfer->getOffsetOrFail(), $paginationTransfer->getLimitOrFail(), 0);

            return [];
        }

        $resources = $this->categoryReader->getCategoryResourceCollection(
            $paginationTransfer,
            $this->buildSortTransfers(),
            $childCategoryIds,
            $this->hasLocale() ? $this->getLocale() : null,
        );

        if ($paginationTransfer->getNbResults() !== null) {
            $this->setCollectionPagination(
                $paginationTransfer->getOffsetOrFail(),
                $paginationTransfer->getLimitOrFail(),
                $paginationTransfer->getNbResultsOrFail(),
            );
        }

        return $resources;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<\Generated\Shared\Transfer\SortTransfer>
     */
    protected function buildSortTransfers(): array
    {
        if (!$this->hasRequest()) {
            return [];
        }

        $sortField = (string)$this->getRequest()->query->get(static::QUERY_PARAM_SORT, '');
        if ($sortField === '') {
            return [];
        }

        $isAscending = !str_starts_with($sortField, static::SORT_DESCENDING_PREFIX);
        $fieldName = ltrim($sortField, static::SORT_DESCENDING_PREFIX);

        if (!isset(static::SORT_FIELD_MAP[$fieldName])) {
            throw $this->exceptionFactory->createUnsupportedSortFieldException($fieldName, array_keys(static::SORT_FIELD_MAP));
        }

        return [
            (new SortTransfer())
                ->setField(static::SORT_FIELD_MAP[$fieldName])
                ->setIsAscending($isAscending),
        ];
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return array<int>|null
     */
    protected function resolveChildCategoryIdsFromFilter(): ?array
    {
        if (!$this->hasRequest()) {
            return null;
        }

        $parentCategoryKey = (string)$this->getRequest()->query->get(static::QUERY_PARAM_PARENT_CATEGORY_KEY, '');
        if ($parentCategoryKey === '') {
            return null;
        }

        $parentCategoryTransfer = $this->categoryReader->findCategoryTransferByCategoryKey($parentCategoryKey);
        if ($parentCategoryTransfer === null || $parentCategoryTransfer->getCategoryNode()?->getIdCategoryNode() === null) {
            throw $this->exceptionFactory->createCategoryNotFoundException();
        }

        return $this->categoryReader->getChildCategoryIdsByIdCategoryNode(
            $parentCategoryTransfer->getCategoryNodeOrFail()->getIdCategoryNodeOrFail(),
        );
    }
}
