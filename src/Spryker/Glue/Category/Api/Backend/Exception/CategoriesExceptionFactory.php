<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Category\Api\Backend\Exception;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class CategoriesExceptionFactory
{
    protected const string MESSAGE_CATEGORY_NOT_FOUND = 'Category not found.';

    protected const string ERROR_CODE_VALIDATION = '422';

    protected const string MESSAGE_UNSUPPORTED_SORT_FIELD = 'Sorting by "%s" is not supported. Supported sort fields: %s.';

    protected const string ERROR_CODE_UNSUPPORTED_SORT_FIELD = '400';

    public function createCategoryNotFoundException(): NotFoundHttpException
    {
        return new NotFoundHttpException(static::MESSAGE_CATEGORY_NOT_FOUND);
    }

    /**
     * @param array<string> $supportedSortFields
     */
    public function createUnsupportedSortFieldException(string $sortField, array $supportedSortFields): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            static::ERROR_CODE_UNSUPPORTED_SORT_FIELD,
            sprintf(static::MESSAGE_UNSUPPORTED_SORT_FIELD, $sortField, implode(', ', $supportedSortFields)),
        );
    }

    /**
     * @param array<string> $errorMessages
     */
    public function createValidationException(array $errorMessages, ?Throwable $previous = null): GlueApiException
    {
        $errors = [];
        foreach ($errorMessages as $errorMessage) {
            $errors[] = [
                'code' => static::ERROR_CODE_VALIDATION,
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'detail' => $errorMessage,
            ];
        }

        return (new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            static::ERROR_CODE_VALIDATION,
            $errorMessages[0] ?? '',
            $previous,
        ))->setErrors($errors);
    }
}
