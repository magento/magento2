<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;

/**
 * Interface \Magento\Framework\View\Element\Message\Renderer\RendererInterface
 *
 * @api
 */
interface RendererInterface
{
    /**
     * Renders message
     *
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     */
    public function render(MessageInterface $message, array $initializationData);
}
