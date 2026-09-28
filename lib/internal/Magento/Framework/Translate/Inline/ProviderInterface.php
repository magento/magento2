<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Translate\Inline;

/**
 * Factory like class to return an instance of the inline translate.
 *
 * @api
 */
interface ProviderInterface
{
    /**
     * Return instance of inline translate class
     *
     * @return \Magento\Framework\Translate\InlineInterface
     */
    public function get();
}
