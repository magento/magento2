<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Exception\NotFoundException;

interface MessageConfigurationInterface
{
    /**
     * Generates specific message based on Exception type and the data it contains
     *
     * @param \Exception $exception
     * @return MessageInterface
     * @throws NotFoundException
     */
    public function generateMessage(\Exception $exception);
}
