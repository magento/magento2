<?php
/**
 * Placeholder Phrase renderer
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Phrase\Renderer;

class Placeholder implements \Magento\Framework\Phrase\RendererInterface
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
            $placeholders = [];
            foreach (array_keys($arguments) as $key) {
                $placeholders[] = "%" . (is_int($key) ? strval($key + 1) : $key);
            }
            $text = str_replace($placeholders, $arguments, $text);
        }

        return $text;
    }
}
