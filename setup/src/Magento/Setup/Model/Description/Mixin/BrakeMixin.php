<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description\Mixin;

/**
 * Add brake html tag to each new line to description
 * @since 2.2.0
 */
class BrakeMixin implements DescriptionMixinInterface
{
    /**
     * Add </br> tag to text after each new line (\r\n)
     *
     * @param string $text
     * @return string
     * @since 2.2.0
     */
    public function apply($text)
    {
        return implode(
            PHP_EOL . '</br>' . PHP_EOL,
            explode(PHP_EOL, trim($text))
        );
    }
}
