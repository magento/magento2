<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Adapter;

/**
 * Interface \Magento\Framework\Search\Adapter\OptionsInterface
 *
 * @api
 */
interface OptionsInterface
{
    /**
     * Get all options
     *
     * @return array
     */
    public function get();
}
