<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Dependency\Facade;

class CategoryToTouchBridge implements CategoryToTouchInterface
{
    /**
     * @var \Spryker\Zed\Touch\Business\TouchFacadeInterface
     */
    protected $touchFacade;

    /**
     * @param \Spryker\Zed\Touch\Business\TouchFacadeInterface $touchFacade
     */
    public function __construct($touchFacade)
    {
        $this->touchFacade = $touchFacade;
    }

    public function touchActive(string $itemType, int $itemId): bool
    {
        return $this->touchFacade->touchActive($itemType, $itemId);
    }

    public function touchDeleted(string $itemType, int $itemId): bool
    {
        return $this->touchFacade->touchDeleted($itemType, $itemId);
    }

    public function isTouchEnabled(): bool
    {
        return $this->touchFacade->isTouchEnabled();
    }
}
