<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;

interface MessageConfigurationInterface
{
    /**
     * @param \Exception $exception
     * @return MessageInterface
     */
    public function createMessage($exception);

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getExceptionClass();
}
