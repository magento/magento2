<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Helper;
use Magento\Shipping\Model\ConfigInterface;

/**
 * Configuration data of carrier
 */
abstract class AbstractConfig implements ConfigInterface
{
    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|string|false
     */
    public function getCode($type, $code = '')
    {
        $codes = $this->getCodes();
        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

}
