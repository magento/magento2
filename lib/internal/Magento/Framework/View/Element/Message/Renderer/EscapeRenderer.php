<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;

/**
 * Class \Magento\Framework\View\Element\Message\Renderer\EscapeRenderer
 *
 * @since 2.0.0
 */
class EscapeRenderer implements RendererInterface
{
    /**
     * complex_renderer
     */
    const CODE = 'escape_renderer';

    /**
     * @var Escaper
     * @since 2.0.0
     */
    private $escaper;

    /**
     * @param Escaper $escaper
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function render(MessageInterface $message, array $initializationData)
    {
        return $this->escaper->escapeHtml($message->getText());
    }
}
