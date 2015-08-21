<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;

class EscapeRenderer implements RendererInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Escaper $escaper
     */
    public function __construct(
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
    }

    /**
     * Renders complex message
     *
     * @param MessageInterface $message
     * @return string
     */
    public function render(MessageInterface $message)
    {
        return $this->escaper->escapeHtml($message->getText());
    }
}
