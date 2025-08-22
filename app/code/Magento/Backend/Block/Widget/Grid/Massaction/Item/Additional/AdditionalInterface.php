<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction\Item\Additional;

/**
 * Backend grid widget massaction item additional action interface
 *
 * @api
 */
interface AdditionalInterface
{
    /**
     * Create additional action from configuration
     *
     * @param array $configuration
     * @return $this
     */
    public function createFromConfiguration(array $configuration);
}
