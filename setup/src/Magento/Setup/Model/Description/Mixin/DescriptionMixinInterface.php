<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description\Mixin;

/**
 * Interface for Description mixin
 * @since 2.2.0
 */
interface DescriptionMixinInterface
{
    /**
     * Apply mixin logic to block of text
     *
     * @param string $text
     * @return string
     * @since 2.2.0
     */
    public function apply($text);
}
