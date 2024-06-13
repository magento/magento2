<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Model\Description\Mixin;

/**
 * Interface for Description mixin
 */
interface DescriptionMixinInterface
{
    /**
     * Apply mixin logic to block of text
     *
     * @param string $text
     * @return string
     */
    public function apply($text);
}
