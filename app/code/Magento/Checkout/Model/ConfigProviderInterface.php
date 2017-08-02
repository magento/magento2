<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

/**
 * Interface ConfigProviderInterface
 * @api
 * @since 2.0.0
 */
interface ConfigProviderInterface
{
    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @since 2.0.0
     */
    public function getConfig();
}
