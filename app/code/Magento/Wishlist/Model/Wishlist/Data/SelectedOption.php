<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Model\Wishlist\Data;

/**
 * DTO represents selected option
 */
class SelectedOption
{
    /**
     * @param string $id
     */
    public function __construct(
        private readonly string $id
    ) {
    }

    /**
     * Get selected option id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
