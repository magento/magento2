<?php
/**
 * Placeholder Phrase renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Renderer;

use Magento\Framework\Phrase\RendererInterface;

/**
 * Class \Magento\Framework\Phrase\Renderer\Placeholder
 *
 * @since 2.0.0
 */
class Placeholder implements RendererInterface
{
    /**
     * Render source text
     *
     * @param [] $source
     * @param [] $arguments
     * @return string
     * @since 2.0.0
     */
    public function render(array $source, array $arguments)
    {
        $text = end($source);

        if ($arguments) {
            $placeholders = array_map([$this, 'keyToPlaceholder'], array_keys($arguments));
            $pairs = array_combine($placeholders, $arguments);
            $text = strtr($text, $pairs);
        }

        return $text;
    }

    /**
     * Get key to placeholder
     *
     * @param string|int $key
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @since 2.0.0
     */
    private function keyToPlaceholder($key)
    {
        return '%' . (is_int($key) ? strval($key + 1) : $key);
    }
}
