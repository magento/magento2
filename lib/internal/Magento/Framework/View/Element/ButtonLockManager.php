<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Element;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class ButtonLockManager implements ArgumentInterface
{
    /**
     * @var ButtonLockInterface[]
     */
    private array $buttonLockPool;

    /**
     * @param array $buttonLockPool
     */
    public function __construct(array $buttonLockPool = [])
    {
        $this->buttonLockPool = $buttonLockPool;
    }

    /**
     * Returns true if the button has to be disabled.
     *
     * @param string $buttonCode
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     */
    public function isDisabled(string $buttonCode): bool
    {
        $result = array_filter($this->buttonLockPool, function ($item) use ($buttonCode) {
            return $item->getCode() === $buttonCode && $item->isDisabled();
        });

        return !empty($result);
    }
}
