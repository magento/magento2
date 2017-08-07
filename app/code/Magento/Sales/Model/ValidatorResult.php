<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Class ValidatorResult
 * @since 2.1.3
 */
class ValidatorResult implements ValidatorResultInterface
{
    /**
     * @var \string[]
     * @since 2.1.3
     */
    private $messages = [];

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function addMessage($message)
    {
        $this->messages[] = (string)$message;
    }

    /**
     * @return bool
     * @since 2.1.3
     */
    public function hasMessages()
    {
        return count($this->messages) > 0;
    }

    /**
     * @return \string[]
     * @since 2.1.3
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
