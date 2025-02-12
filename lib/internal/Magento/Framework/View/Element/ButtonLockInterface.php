<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element;

use Magento\Framework\Exception\InputException;

interface ButtonLockInterface
{
    /**
     * Get button code
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * If the button should be temporary disabled
     *
     * @return bool
     * @throws InputException
     */
    public function isDisabled(): bool;
}
