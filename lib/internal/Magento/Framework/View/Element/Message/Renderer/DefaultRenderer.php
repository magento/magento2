<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;

class DefaultRenderer implements RendererInterface
{
    /**
     * complex_renderer
     */
    const CODE = 'default_renderer';

    /**
     * Renders complex message
     *
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(MessageInterface $message, array $initializationData)
    {
        return $message->getText();
    }
}
