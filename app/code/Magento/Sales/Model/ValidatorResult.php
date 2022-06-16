<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Validation result messages class
 */
class ValidatorResult implements ValidatorResultInterface
{
    /**
     * @var \string[]
     */
    private $messages;

    /**
     * @param array $messages
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    /**
     * @inheritdoc
     */
    public function addMessage($message)
    {
        $this->messages[] = (string)$message;
    }

    /**
     * @inheritdoc
     */
    public function hasMessages()
    {
        return count($this->messages) > 0;
    }

    /**
     * @inheritdoc
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
