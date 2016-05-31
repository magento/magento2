<?php
/**
 * Placeholder Phrase renderer
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Phrase\Renderer;

use Magento\Framework\Phrase\RendererInterface;

class Placeholder implements RendererInterface
{
    /**
     * Render source text
     *
     * @param [] $source
     * @param [] $arguments
     * @return string
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
     */
    private function keyToPlaceholder($key)
    {
        return '%' . (is_int($key) ? strval($key + 1) : $key);
    }
}
