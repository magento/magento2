<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart\Data;

/**
 * DTO for quote item selected option
 */
class SelectedOption
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * Get selected option ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
