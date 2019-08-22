<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Class ValidatorResult
 */
class ValidatorResult implements ValidatorResultInterface
{
    /**
     * @var \string[]
     */
    private $messages = [];

    /**
     * @inheritdoc
     */
    public function addMessage($message)
    {
        $this->messages[] = (string)$message;
    }

    /**
     * @return bool
     */
    public function hasMessages()
    {
        return count($this->messages) > 0;
    }

    /**
     * @return \string[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
