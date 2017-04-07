<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\CustomerData;

/**
 * Js layout data provider pool interface
 *
 * @api
 */
interface JsLayoutDataProviderPoolInterface
{
    /**
     * Get data
     *
     * @return array
     */
    public function getData();
}
