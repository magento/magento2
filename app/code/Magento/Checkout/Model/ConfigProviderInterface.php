<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Model;

/**
 * Interface ConfigProviderInterface
 * @api
 * @since 100.0.2
 */
interface ConfigProviderInterface
{
    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig();
}
