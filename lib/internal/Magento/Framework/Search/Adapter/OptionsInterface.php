<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter;

/**
 * Interface \Magento\Framework\Search\Adapter\OptionsInterface
 *
 * @since 2.0.0
 */
interface OptionsInterface
{
    /**
     * Get all options
     *
     * @return array
     * @since 2.0.0
     */
    public function get();
}
