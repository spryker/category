<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business\Validator\Util;

use Generated\Shared\Transfer\ErrorCollectionTransfer;

interface ErrorAdderInterface
{
    /**
     * @param \Generated\Shared\Transfer\ErrorCollectionTransfer $errorCollectionTransfer
     * @param string|int $entityIdentifier
     * @param string $error
     * @param array<string, int|string> $parameters
     *
     * @return \Generated\Shared\Transfer\ErrorCollectionTransfer
     */
    public function addError(
        ErrorCollectionTransfer $errorCollectionTransfer,
        int|string $entityIdentifier,
        string $error,
        array $parameters = []
    ): ErrorCollectionTransfer;
}
