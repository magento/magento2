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
 * @method setCarrier($carrier)
 * @method string getCarrier()
 * @method setCarrierTitle($carrier_title)
 * @method string  getMethod()
 * @method setMethod($method)
 * @method setMethodTitle($method_title)
 * @method double getCost()
 * @method setCost($cost)
 * @method double getPrice()
 * @method setPrice($price)
 */
class Mage_Shipping_Model_Carrier_Service_Method extends Varien_Object
{
    public function createRateResultMethod()
    {
        /** @var $method Mage_Shipping_Model_Rate_Result_Method */
        $method = Mage::getModel('Mage_Shipping_Model_Rate_Result_Method');

        $method->setCarrier($this->getCarrier());
        $method->setCarrierTitle($this->getCarrierTitle());

        $method->setMethod($this->getMethod());
        $method->setMethodTitle($this->getMethodTitle());

        $method->setPrice($this->getPrice());
        $method->setCost($this->getCost());

        return $method;
    }

    protected function getCarrierTitle()
    {
        return $this->_getData('carrierTitle');
    }

    protected function getMethodTitle()
    {
        return $this->_getData('methodTitle');
    }
}