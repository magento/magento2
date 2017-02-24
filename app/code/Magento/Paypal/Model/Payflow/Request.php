<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow;

/**
 * Payflow Link request model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Request extends \Magento\Framework\DataObject
{
    /**
     * Set/Get attribute wrapper
     * Also add length path if key contains = or &
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __call($method, $args)
    {
        $key = $this->_underscore(substr($method, 3));
        if (isset($args[0]) && (strstr($args[0], '=') || strstr($args[0], '&'))) {
            $key .= '[' . strlen($args[0]) . ']';
        }
        switch (substr($method, 0, 3)) {
            case 'get':
                //\Magento\Framework\Profiler::start('GETTER: '.get_class($this).'::'.$method);
                $data = $this->getData($key, isset($args[0]) ? $args[0] : null);
                //\Magento\Framework\Profiler::stop('GETTER: '.get_class($this).'::'.$method);
                return $data;

            case 'set':
                //\Magento\Framework\Profiler::start('SETTER: '.get_class($this).'::'.$method);
                $result = $this->setData($key, isset($args[0]) ? $args[0] : null);
                //\Magento\Framework\Profiler::stop('SETTER: '.get_class($this).'::'.$method);
                return $result;

            case 'uns':
                //\Magento\Framework\Profiler::start('UNS: '.get_class($this).'::'.$method);
                $result = $this->unsetData($key);
                //\Magento\Framework\Profiler::stop('UNS: '.get_class($this).'::'.$method);
                return $result;

            case 'has':
                //\Magento\Framework\Profiler::start('HAS: '.get_class($this).'::'.$method);
                //\Magento\Framework\Profiler::stop('HAS: '.get_class($this).'::'.$method);
                return isset($this->_data[$key]);
        }
        throw new \Magento\Framework\Exception\LocalizedException(
            __("Invalid method %1::%2", get_class($this), $method)
        );
    }
}
