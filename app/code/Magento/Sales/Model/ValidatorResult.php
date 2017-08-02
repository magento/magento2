<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Class ValidatorResult
 * @since 2.2.0
 */
class ValidatorResult implements ValidatorResultInterface
{
    /**
     * @var \string[]
     * @since 2.2.0
     */
    private $messages = [];

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function addMessage($message)
    {
        $this->messages[] = (string)$message;
    }

    /**
     * @return bool
     * @since 2.2.0
     */
    public function hasMessages()
    {
        return count($this->messages) > 0;
    }

    /**
     * @return \string[]
     * @since 2.2.0
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
