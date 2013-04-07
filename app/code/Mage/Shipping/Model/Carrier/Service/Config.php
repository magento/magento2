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
 * @category    Mage
 * @package     Mage_Shipping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Shipping_Model_Carrier_Service_Config
{
    private $_config;

    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    /**
     * Retrieve information from carrier configuration
     *
     * @param   string $field
     * @return  mixed
     */
    public function getConfigData($field)
    {
        if (empty($this->_config)) {
            return null;
        }
        $tokens = explode('/', $field);
        return $this->_getConfigData($tokens, $this->_config);
    }

    private function _getConfigData(array $tokens, $config)
    {
        if (empty($tokens)) {
            return $config;
        }
        $index = array_shift($tokens);
        if (!array_key_exists($index, $config)) {
            return null;
        }
        $configValue = $config[$index];
        return $this->_getConfigData($tokens, $configValue);
    }

    /**
     * Retrieve config flag for store by field
     *
     * @param string $field
     * @return bool
     */
    public function getConfigFlag($field)
    {
        $flag = strtolower($this->getConfigData($field));
        if (!empty($flag) && 'false' !== $flag) {
            return true;
        } else {
            return false;
        }
    }
}