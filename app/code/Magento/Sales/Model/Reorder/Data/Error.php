<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Reorder\Data;

/**
 * DTO represent error item
 */
class Error
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $code;

    /**
     * @param string $message
     * @param string $code
     */
    public function __construct(string $message, string $code)
    {
        $this->message = $message;
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}
