<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model\Payflow;

/**
 * Payflow Link request model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Request extends \Magento\Framework\Object
{
    /**
     * Set/Get attribute wrapper
     * Also add length path if key contains = or &
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Magento\Framework\Exception
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
        throw new \Magento\Framework\Exception(
            "Invalid method " . get_class($this) . "::" . $method . "(" . print_r($args, 1) . ")"
        );
    }
}
