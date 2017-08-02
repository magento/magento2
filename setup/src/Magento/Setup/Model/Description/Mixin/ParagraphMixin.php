<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description\Mixin;

/**
 * Add paragraph html tag to description
 * @since 2.2.0
 */
class ParagraphMixin implements DescriptionMixinInterface
{
    /**
     * Wrap each new line with <p></p> tags
     *
     * @param string $text
     * @return string
     * @since 2.2.0
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
