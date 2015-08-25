<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;

interface RendererInterface
{
    /**
     * Renders complex message
     *
     * @param MessageInterface $message
     * @return string
     */
    public function render(MessageInterface $message);

    /**
     * Initialize renderer with state
     *
     * @param array $data
     * @return void
     */
    public function initialize(array $data);
}
