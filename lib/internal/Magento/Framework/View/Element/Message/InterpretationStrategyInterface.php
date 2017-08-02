<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message;

use Magento\Framework\Message\MessageInterface;

/**
 * Interface \Magento\Framework\View\Element\Message\InterpretationStrategyInterface
 *
 * @since 2.0.0
 */
interface InterpretationStrategyInterface
{
    /**
     * Interpret message
     *
     * @param MessageInterface $message
     * @return string
     * @since 2.0.0
     */
    public function interpret(MessageInterface $message);
}
