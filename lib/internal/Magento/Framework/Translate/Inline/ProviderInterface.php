<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate\Inline;

/**
 * Factory like class to return an instance of the inline translate.
 * @since 2.0.0
 */
interface ProviderInterface
{
    /**
     * Return instance of inline translate class
     *
     * @return \Magento\Framework\Translate\InlineInterface
     * @since 2.0.0
     */
    public function get();
}
