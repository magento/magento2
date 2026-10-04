<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
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
