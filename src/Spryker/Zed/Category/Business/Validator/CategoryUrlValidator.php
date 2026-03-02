<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Validator;

use Generated\Shared\Transfer\CategoryUrlCollectionRequestTransfer;
use Generated\Shared\Transfer\CategoryUrlCollectionResponseTransfer;
use Generated\Shared\Transfer\ErrorCollectionTransfer;
use Spryker\Zed\Category\Business\Validator\Rule\TerminationAwareValidatorRuleInterface;

class CategoryUrlValidator implements CategoryUrlValidatorInterface
{
    /**
     * @var list<\Spryker\Zed\Category\Business\Validator\Rule\CategoryUrl\CategoryUrlValidatorRuleInterface>
     */
    protected array $categoryUrlValidatorRules;

    /**
     * @param list<\Spryker\Zed\Category\Business\Validator\Rule\CategoryUrl\CategoryUrlValidatorRuleInterface> $categoryUrlValidatorRules
     */
    public function __construct(array $categoryUrlValidatorRules)
    {
        $this->categoryUrlValidatorRules = $categoryUrlValidatorRules;
    }

    public function validateCollection(CategoryUrlCollectionRequestTransfer $categoryUrlRequestCollectionTransfer): CategoryUrlCollectionResponseTransfer
    {
        $categoryTransfers = $categoryUrlRequestCollectionTransfer->getCategories();
        $categoryUrlCollectionResponseTransfer = (new CategoryUrlCollectionResponseTransfer())->setCategories($categoryTransfers);

        $initialErrorCollectionTransfer = (new ErrorCollectionTransfer());
        foreach ($this->categoryUrlValidatorRules as $categoryUrlValidatorRule) {
            $initialErrorCollectionTransfer->setErrors($categoryUrlCollectionResponseTransfer->getErrors());
            $postValidationErrorCollectionTransfer = $categoryUrlValidatorRule->validate($categoryTransfers);

            $categoryUrlCollectionResponseTransfer = $this->mergeErrors(
                $categoryUrlCollectionResponseTransfer,
                $postValidationErrorCollectionTransfer,
            );

            if ($categoryUrlValidatorRule instanceof TerminationAwareValidatorRuleInterface) {
                $categoryUrlValidatorRule->isTerminated(
                    $initialErrorCollectionTransfer->getErrors(),
                    $postValidationErrorCollectionTransfer->getErrors(),
                );

                break;
            }
        }

        return $categoryUrlCollectionResponseTransfer;
    }

    protected function mergeErrors(
        CategoryUrlCollectionResponseTransfer $categoryUrlCollectionResponseTransfer,
        ErrorCollectionTransfer $postValidationErrorCollectionTransfer
    ): CategoryUrlCollectionResponseTransfer {
        foreach ($postValidationErrorCollectionTransfer->getErrors() as $errorTransfer) {
            $categoryUrlCollectionResponseTransfer->addError($errorTransfer);
        }

        return $categoryUrlCollectionResponseTransfer;
    }
}
