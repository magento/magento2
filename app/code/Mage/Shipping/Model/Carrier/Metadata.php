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
/**
 * Class that stores/accesses carrier metadata so it can be easily passed to Rates calculation model
 */
class Mage_Shipping_Model_Carrier_Metadata
{
    protected $_carrierConfig;
    protected $_carrierCode;

    /**
     * @param $carrierCode string
     * @param $carrierConfig array
     */
    public function __construct($carrierCode, $carrierConfig)
    {
        $this->_carrierCode = $carrierCode;
        $this->_carrierConfig = $carrierConfig;
    }

    public function getSubscriberExtensionId()
    {
        return $this->getCarrierConfig('subscriber');
    }

    public function getCarrierConfig($key = null)
    {
        if ($key == null) {
            return $this->_carrierConfig;
        } else {
            return $this->_carrierConfig[$key];
        }
    }

    public function getCarrierCode()
    {
        return $this->_carrierCode;
    }

}