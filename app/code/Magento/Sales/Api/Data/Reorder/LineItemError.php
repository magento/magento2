<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data\Reorder;

/**
 * DTO represent Cart line item error
 */
class LineItemError
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @var string
     */
    private $message;

    /**
     * @param string $sku
     * @param string $message
     */
    public function __construct(string $sku, string $message)
    {
        $this->sku = $sku;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
