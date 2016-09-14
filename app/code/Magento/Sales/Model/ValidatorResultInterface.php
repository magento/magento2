<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Interface ValidatorResultInterface
 */
interface ValidatorResultInterface
{
    /**
     * @param \Magento\Framework\Phrase
     * @return void
     */
    public function addMessage(\Magento\Framework\Phrase $message);

    /**
     * @return bool
     */
    public function hasMessages();

    /**
     * @return \Magento\Framework\Phrase[]
     */
    public function getMessages();
}
