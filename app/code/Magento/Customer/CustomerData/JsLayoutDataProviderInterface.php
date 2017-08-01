<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\CustomerData;

/**
 * Js layout data provider interface
 *
 * @api
 * @since 2.0.0
 */
interface JsLayoutDataProviderInterface
{
    /**
     * Get data
     *
     * @return array
     * @since 2.0.0
     */
    public function getData();
}
