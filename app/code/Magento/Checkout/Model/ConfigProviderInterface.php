<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

/**
 * Interface ConfigProviderInterface
 * @api
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
