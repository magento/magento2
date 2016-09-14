<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Class ValidatorResult
 */
class ValidatorResult implements ValidatorResultInterface
{
    /**
     * @var \Magento\Framework\Phrase[]
     */
    private $messages = [];

    /**
     * @param \Magento\Framework\Phrase
     * @return void
     */
    public function addMessage(\Magento\Framework\Phrase $message)
    {
        $this->messages[] = $message;
    }

    /**
     * @return bool
     */
    public function hasMessages()
    {
        return count($this->messages) > 0;
    }

    /**
     * @return \Magento\Framework\Phrase[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
