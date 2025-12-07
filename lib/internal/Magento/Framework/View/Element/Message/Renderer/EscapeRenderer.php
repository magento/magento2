<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;

class EscapeRenderer implements RendererInterface
{
    /**
     * complex_renderer
     */
    const CODE = 'escape_renderer';

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
     * @param array $initializationData
     * @return string
     */
    public function render(MessageInterface $message, array $initializationData)
    {
        return $this->escaper->escapeHtml($message->getText());
    }
}
