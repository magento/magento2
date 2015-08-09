<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model;

use Magento\Shipping\Model\Carrier\AbstractCarrierInterface;

/**
 * Interface ConfigInterface
 *
 * Used for getting Carrier Configuration Options in Helper
 *
 */
interface ConfigInterface
{

    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|string|false
     */
    public function getCode($type, $code = '');

    /**
     * Get configuration data of carrier
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    function getCodes();

}