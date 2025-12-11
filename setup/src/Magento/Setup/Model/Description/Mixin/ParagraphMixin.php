<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Model\Description\Mixin;

/**
 * Add paragraph html tag to description
 */
class ParagraphMixin implements DescriptionMixinInterface
{
    /**
     * Wrap each new line with <p></p> tags
     *
     * @param string $text
     * @return string
     */
    public function apply($text)
    {
        return '<p>'
            . implode(
                '</p>' . PHP_EOL . '<p>',
                explode(PHP_EOL, trim($text))
            )
            . '</p>';
    }
}
