<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate\Inline;

/**
 * Factory like class to return an instance of the inline translate.
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
