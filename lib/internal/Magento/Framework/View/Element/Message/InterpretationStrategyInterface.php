<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message;

use Magento\Framework\Message\MessageInterface;

interface InterpretationStrategyInterface
{
    /**
     * Interpret message
     *
     * @param MessageInterface $message
     * @return string
     */
    public function interpret(MessageInterface $message);
}
