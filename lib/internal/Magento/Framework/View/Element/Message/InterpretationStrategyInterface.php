<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\Message;

use Magento\Framework\Message\MessageInterface;

/**
 * Interface \Magento\Framework\View\Element\Message\InterpretationStrategyInterface
 *
 * @api
 */
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
