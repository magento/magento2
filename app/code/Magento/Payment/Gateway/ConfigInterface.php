<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway;

/**
 * Interface ConfigInterface
 * @package Magento\Payment\Gateway
 * @api
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getValue($field, $storeId = null);

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     * @since 2.0.0
     */
    public function setMethodCode($methodCode);

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     * @since 2.0.0
     */
    public function setPathPattern($pathPattern);
}
