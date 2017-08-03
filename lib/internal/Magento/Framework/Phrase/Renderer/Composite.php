<?php
/**
 * Composite Phrase renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Renderer;

use Magento\Framework\Phrase\RendererInterface;

/**
 * Class \Magento\Framework\Phrase\Renderer\Composite
 *
 * @since 2.0.0
 */
class Composite implements RendererInterface
{
    /**
     * @var RendererInterface[]
     * @since 2.0.0
     */
    protected $_renderers;

    /**
     * @param \Magento\Framework\Phrase\RendererInterface[] $renderers
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function __construct(array $renderers)
    {
        foreach ($renderers as $renderer) {
            if (!$renderer instanceof RendererInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Instance of the phrase renderer is expected, got %s instead.', get_class($renderer))
                );
            }
        }
        $this->_renderers = $renderers;
    }

    /**
     * Render source text
     *
     * @param [] $source
     * @param [] $arguments
     * @return string
     * @since 2.0.0
     */
    public function render(array $source, array $arguments = [])
    {
        $result = $source;
        foreach ($this->_renderers as $render) {
            $result[] = $render->render($result, $arguments);
        }
        return end($result);
    }
}
