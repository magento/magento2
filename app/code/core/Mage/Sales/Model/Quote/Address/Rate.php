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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Enter description here ...
 *
 * @method Mage_Sales_Model_Resource_Quote_Address_Rate _getResource()
 * @method Mage_Sales_Model_Resource_Quote_Address_Rate getResource()
 * @method int getAddressId()
 * @method Mage_Sales_Model_Quote_Address_Rate setAddressId(int $value)
 * @method string getCreatedAt()
 * @method Mage_Sales_Model_Quote_Address_Rate setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Mage_Sales_Model_Quote_Address_Rate setUpdatedAt(string $value)
 * @method string getCarrier()
 * @method Mage_Sales_Model_Quote_Address_Rate setCarrier(string $value)
 * @method string getCarrierTitle()
 * @method Mage_Sales_Model_Quote_Address_Rate setCarrierTitle(string $value)
 * @method string getCode()
 * @method Mage_Sales_Model_Quote_Address_Rate setCode(string $value)
 * @method string getMethod()
 * @method Mage_Sales_Model_Quote_Address_Rate setMethod(string $value)
 * @method string getMethodDescription()
 * @method Mage_Sales_Model_Quote_Address_Rate setMethodDescription(string $value)
 * @method float getPrice()
 * @method Mage_Sales_Model_Quote_Address_Rate setPrice(float $value)
 * @method string getErrorMessage()
 * @method Mage_Sales_Model_Quote_Address_Rate setErrorMessage(string $value)
 * @method string getMethodTitle()
 * @method Mage_Sales_Model_Quote_Address_Rate setMethodTitle(string $value)
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Quote_Address_Rate extends Mage_Shipping_Model_Rate_Abstract
{
    protected $_address;

    protected function _construct()
    {
        $this->_init('Mage_Sales_Model_Resource_Quote_Address_Rate');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->getAddress()) {
            $this->setAddressId($this->getAddress()->getId());
        }
        return $this;
    }

    public function setAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_address = $address;
        return $this;
    }

    public function getAddress()
    {
        return $this->_address;
    }

    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate)
    {
        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
            $this
                ->setCode($rate->getCarrier().'_error')
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setErrorMessage($rate->getErrorMessage())
            ;
        } elseif ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {
            $this
                ->setCode($rate->getCarrier().'_'.$rate->getMethod())
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setMethod($rate->getMethod())
                ->setMethodTitle($rate->getMethodTitle())
                ->setMethodDescription($rate->getMethodDescription())
                ->setPrice($rate->getPrice())
            ;
        }
        return $this;
    }
}
