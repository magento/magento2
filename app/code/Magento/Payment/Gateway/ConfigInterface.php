<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway;

/**
 * Interface ConfigInterface
 * @package Magento\Payment\Gateway
 * @api
 */
interface ConfigInterface
{
    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null);

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     */
    public function setMethodCode($methodCode);

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern);
}
