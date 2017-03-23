<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\CustomerData;

/**
 * Js layout data provider interface
 */
interface JsLayoutDataProviderInterface
{
    /**
     * Get data
     *
     * @return array
     */
    public function getData();
}
