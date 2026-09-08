<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Processor;

use ArrayObject;
use Generated\Api\Backend\CategoriesBackendResource;
use Generated\Shared\Transfer\CategoryCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\CategoryCollectionRequestTransfer;
use Generated\Shared\Transfer\CategoryCollectionResponseTransfer;
use Generated\Shared\Transfer\CategoryNodeUrlCriteriaTransfer;
use Generated\Shared\Transfer\CategoryTransfer;
use Generated\Shared\Transfer\CategoryUrlCollectionRequestTransfer;
use Generated\Shared\Transfer\NodeTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Glue\Category\Api\Backend\Exception\CategoriesExceptionFactory;
use Spryker\Glue\Category\Api\Backend\Mapper\CategoryRequestMapperInterface;
use Spryker\Glue\Category\Api\Backend\Reader\CategoryReaderInterface;
use Spryker\Glue\Category\Api\Backend\Reader\CategoryWriteContextReaderInterface;
use Spryker\Glue\Category\Api\Backend\Validator\CategoryWriteValidatorInterface;
use Spryker\Zed\Category\Business\CategoryFacadeInterface;
use Spryker\Zed\Category\Business\Exception\CategoryUrlExistsException;
use Spryker\Zed\Kernel\Persistence\EntityManager\InstancePoolingTrait;
use Spryker\Zed\Url\Business\Exception\UrlExistsException;

class CategoriesBackendProcessor extends AbstractBackendProcessor
{
    use InstancePoolingTrait;

    protected const string URI_VARIABLE_CATEGORY_KEY = 'categoryKey';

    protected const string ERROR_URL_COLLISION = 'The URL for locale "%s" could not be created because a conflicting URL already exists; the category was not created.';

    public function __construct(
        protected CategoryFacadeInterface $categoryFacade,
        protected CategoryReaderInterface $categoryReader,
        protected CategoryWriteContextReaderInterface $categoryWriteContextReader,
        protected CategoryRequestMapperInterface $categoryRequestMapper,
        protected CategoryWriteValidatorInterface $categoryWriteValidator,
        protected CategoriesExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPost(mixed $data): CategoriesBackendResource
    {
        /** @var \Generated\Api\Backend\CategoriesBackendResource $categoriesBackendResource */
        $categoriesBackendResource = $data;

        $categoryWriteContextTransfer = $this->categoryWriteContextReader->getCategoryWriteContext($categoriesBackendResource);

        $errors = $this->categoryWriteValidator->validateCreate($categoriesBackendResource, $categoryWriteContextTransfer);
        if ($errors !== []) {
            throw $this->exceptionFactory->createValidationException($errors);
        }

        $categoryTransfer = $this->categoryRequestMapper->mapResourceToNewCategoryTransfer($categoriesBackendResource, $categoryWriteContextTransfer);

        $categoryCollectionRequestTransfer = (new CategoryCollectionRequestTransfer())
            ->addCategory($categoryTransfer)
            ->setIsTransactional(true);

        try {
            $categoryCollectionResponseTransfer = $this->categoryFacade->createCategoryCollection($categoryCollectionRequestTransfer);
        } catch (UrlExistsException | CategoryUrlExistsException $urlExistsException) {
            throw $this->exceptionFactory->createValidationException([$urlExistsException->getMessage()], $urlExistsException);
        }

        $this->assertResponseHasNoErrors($categoryCollectionResponseTransfer);

        $categoryKey = $data->categoryKey;
        $this->verifyCreatedCategoryUrls((string)$categoryKey);

        return $this->getCategoryResourceOrFail((string)$categoryKey);
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPatch(mixed $data): CategoriesBackendResource
    {
        /** @var \Generated\Api\Backend\CategoriesBackendResource $categoriesBackendResource */
        $categoriesBackendResource = $data;

        $categoryKey = (string)$this->getUriVariable(static::URI_VARIABLE_CATEGORY_KEY);

        $currentCategoryTransfer = $this->categoryReader->findCategoryTransferByCategoryKey($categoryKey);
        if ($currentCategoryTransfer === null) {
            throw $this->exceptionFactory->createCategoryNotFoundException();
        }

        $categoryWriteContextTransfer = $this->categoryWriteContextReader->getCategoryWriteContext($categoriesBackendResource);

        $errors = $this->categoryWriteValidator->validateUpdate($categoriesBackendResource, $currentCategoryTransfer, $categoryWriteContextTransfer);
        if ($errors !== []) {
            throw $this->exceptionFactory->createValidationException($errors);
        }

        $categoryTransfer = $this->categoryRequestMapper->mapResourceToExistingCategoryTransfer($categoriesBackendResource, $currentCategoryTransfer, $categoryWriteContextTransfer);

        $categoryCollectionRequestTransfer = (new CategoryCollectionRequestTransfer())
            ->addCategory($categoryTransfer)
            ->setIsTransactional(true);

        try {
            $categoryCollectionResponseTransfer = $this->categoryFacade->updateCategoryCollection($categoryCollectionRequestTransfer);
        } catch (UrlExistsException | CategoryUrlExistsException $urlExistsException) {
            throw $this->exceptionFactory->createValidationException([$urlExistsException->getMessage()], $urlExistsException);
        }

        $this->assertResponseHasNoErrors($categoryCollectionResponseTransfer);

        return $this->getCategoryResourceOrFail($categoryKey);
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processDelete(): mixed
    {
        $categoryKey = (string)$this->getUriVariable(static::URI_VARIABLE_CATEGORY_KEY);

        $currentCategoryTransfer = $this->categoryReader->findCategoryTransferByCategoryKey($categoryKey);
        if ($currentCategoryTransfer === null) {
            throw $this->exceptionFactory->createCategoryNotFoundException();
        }

        $errors = $this->categoryWriteValidator->validateDelete($currentCategoryTransfer);
        if ($errors !== []) {
            throw $this->exceptionFactory->createValidationException($errors);
        }

        $categoryCollectionResponseTransfer = $this->categoryFacade->deleteCategoryCollection(
            (new CategoryCollectionDeleteCriteriaTransfer())
                ->addCategoryKey($categoryKey)
                ->setIsTransactional(true),
        );

        $this->assertResponseHasNoErrors($categoryCollectionResponseTransfer);

        return null;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function verifyCreatedCategoryUrls(string $categoryKey): void
    {
        $categoryTransfer = $this->findFreshCategoryTransferByCategoryKey($categoryKey);
        if ($categoryTransfer === null) {
            return;
        }

        $missingUrlNodeIdsByLocaleId = $this->findMissingUrlNodeIdsByLocaleId($categoryTransfer);
        if ($missingUrlNodeIdsByLocaleId === []) {
            return;
        }

        $this->repairMissingCategoryUrls($categoryTransfer, $missingUrlNodeIdsByLocaleId);

        $missingUrlNodeIdsByLocaleId = $this->findMissingUrlNodeIdsByLocaleId($categoryTransfer);
        if ($missingUrlNodeIdsByLocaleId === []) {
            return;
        }

        $this->categoryFacade->deleteCategoryCollection(
            (new CategoryCollectionDeleteCriteriaTransfer())
                ->addCategoryKey($categoryKey)
                ->setIsTransactional(true),
        );

        throw $this->exceptionFactory->createValidationException(
            $this->buildUrlCollisionErrorMessages($categoryTransfer, array_keys($missingUrlNodeIdsByLocaleId)),
        );
    }

    /**
     * @return array<int, array<int>>
     */
    protected function findMissingUrlNodeIdsByLocaleId(CategoryTransfer $categoryTransfer): array
    {
        $localeIds = [];
        foreach ($categoryTransfer->getLocalizedAttributes() as $categoryLocalizedAttributesTransfer) {
            $localeIds[] = $categoryLocalizedAttributesTransfer->getLocaleOrFail()->getIdLocaleOrFail();
        }

        $categoryNodeIds = [];
        foreach ($categoryTransfer->getNodeCollection()?->getNodes() ?? [] as $nodeTransfer) {
            if ($nodeTransfer->getIdCategoryNode() !== null) {
                $categoryNodeIds[] = $nodeTransfer->getIdCategoryNodeOrFail();
            }
        }

        if ($localeIds === [] || $categoryNodeIds === []) {
            return [];
        }

        $urlTransfers = $this->categoryFacade->getCategoryNodeUrls(
            (new CategoryNodeUrlCriteriaTransfer())->setCategoryNodeIds($categoryNodeIds),
        );

        $existingUrlKeys = [];
        foreach ($urlTransfers as $urlTransfer) {
            $existingUrlKeys[$urlTransfer->getFkResourceCategorynode() . ':' . $urlTransfer->getFkLocale()] = true;
        }

        $missingUrlNodeIdsByLocaleId = [];
        foreach ($categoryNodeIds as $idCategoryNode) {
            foreach ($localeIds as $idLocale) {
                if (!isset($existingUrlKeys[$idCategoryNode . ':' . $idLocale])) {
                    $missingUrlNodeIdsByLocaleId[$idLocale][] = $idCategoryNode;
                }
            }
        }

        return $missingUrlNodeIdsByLocaleId;
    }

    /**
     * @param array<int, array<int>> $missingUrlNodeIdsByLocaleId
     */
    protected function repairMissingCategoryUrls(CategoryTransfer $categoryTransfer, array $missingUrlNodeIdsByLocaleId): void
    {
        $localizedAttributesIndexedByIdLocale = [];
        foreach ($categoryTransfer->getLocalizedAttributes() as $categoryLocalizedAttributesTransfer) {
            $localizedAttributesIndexedByIdLocale[$categoryLocalizedAttributesTransfer->getLocaleOrFail()->getIdLocaleOrFail()] = $categoryLocalizedAttributesTransfer;
        }

        $localizedAttributesByNodeId = [];
        foreach ($missingUrlNodeIdsByLocaleId as $idLocale => $categoryNodeIds) {
            foreach ($categoryNodeIds as $idCategoryNode) {
                if (isset($localizedAttributesIndexedByIdLocale[$idLocale])) {
                    $localizedAttributesByNodeId[$idCategoryNode][] = $localizedAttributesIndexedByIdLocale[$idLocale];
                }
            }
        }

        $categoryUrlCollectionRequestTransfer = (new CategoryUrlCollectionRequestTransfer())->setIsTransactional(false);
        foreach ($localizedAttributesByNodeId as $idCategoryNode => $categoryLocalizedAttributesTransfers) {
            $urlCategoryTransfer = (new CategoryTransfer())
                ->setIdCategory($categoryTransfer->getIdCategory())
                ->setCategoryKey($categoryTransfer->getCategoryKey())
                ->setCategoryNode((new NodeTransfer())->setIdCategoryNode($idCategoryNode))
                ->setLocalizedAttributes(new ArrayObject($categoryLocalizedAttributesTransfers));

            $categoryUrlCollectionRequestTransfer->addCategory($urlCategoryTransfer);
        }

        try {
            $this->categoryFacade->createCategoryUrlCollection($categoryUrlCollectionRequestTransfer);
        } catch (UrlExistsException | CategoryUrlExistsException $urlExistsException) {
        }
    }

    /**
     * @param array<int> $missingLocaleIds
     *
     * @return array<string>
     */
    protected function buildUrlCollisionErrorMessages(CategoryTransfer $categoryTransfer, array $missingLocaleIds): array
    {
        $localeNamesIndexedByIdLocale = [];
        foreach ($categoryTransfer->getLocalizedAttributes() as $categoryLocalizedAttributesTransfer) {
            $localeTransfer = $categoryLocalizedAttributesTransfer->getLocaleOrFail();
            $localeNamesIndexedByIdLocale[$localeTransfer->getIdLocaleOrFail()] = $localeTransfer->getLocaleNameOrFail();
        }

        $errorMessages = [];
        foreach ($missingLocaleIds as $idLocale) {
            $errorMessages[] = sprintf(static::ERROR_URL_COLLISION, $localeNamesIndexedByIdLocale[$idLocale] ?? (string)$idLocale);
        }

        return $errorMessages;
    }

    protected function findFreshCategoryTransferByCategoryKey(string $categoryKey): ?CategoryTransfer
    {
        $isInstancePoolingStateChanged = $this->disableInstancePooling();

        try {
            return $this->categoryReader->findCategoryTransferByCategoryKey($categoryKey);
        } finally {
            if ($isInstancePoolingStateChanged) {
                $this->enableInstancePooling();
            }
        }
    }

    /**
     * @see findFreshCategoryTransferByCategoryKey()
     */
    protected function findFreshCategoryResourceByCategoryKey(string $categoryKey): ?CategoriesBackendResource
    {
        $isInstancePoolingStateChanged = $this->disableInstancePooling();

        try {
            return $this->categoryReader->findCategoryResourceByCategoryKey($categoryKey);
        } finally {
            if ($isInstancePoolingStateChanged) {
                $this->enableInstancePooling();
            }
        }
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getCategoryResourceOrFail(string $categoryKey): CategoriesBackendResource
    {
        $categoriesBackendResource = $this->findFreshCategoryResourceByCategoryKey($categoryKey);

        if ($categoriesBackendResource === null) {
            throw $this->exceptionFactory->createCategoryNotFoundException();
        }

        return $categoriesBackendResource;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function assertResponseHasNoErrors(CategoryCollectionResponseTransfer $categoryCollectionResponseTransfer): void
    {
        if ($categoryCollectionResponseTransfer->getErrors()->count() === 0) {
            return;
        }

        $errorMessages = [];
        foreach ($categoryCollectionResponseTransfer->getErrors() as $errorTransfer) {
            $errorMessages[] = (string)$errorTransfer->getMessage();
        }

        throw $this->exceptionFactory->createValidationException($errorMessages);
    }
}
