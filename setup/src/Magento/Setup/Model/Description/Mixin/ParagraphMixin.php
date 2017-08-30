<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
