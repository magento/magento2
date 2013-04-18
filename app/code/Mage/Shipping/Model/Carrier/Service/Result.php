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
class Mage_Shipping_Model_Carrier_Service_Result
{
    /** @var Mage_Shipping_Model_Rate_Result */
    protected $_rateResult;

    /**
     * @param Mage_Shipping_Model_Rate_Result $rateResult
     */
    public function __construct()
    {
        $this->_rateResult = new Mage_Shipping_Model_Rate_Result();
    }

    /**
     * Takes an array of service results from a web service and wraps their contents in a
     * Mage_Shipping_Model_Rate_Result object.
     * @param $serviceResults array
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function createRateResult($serviceResults)
    {
        /** @var $result Mage_Shipping_Model_Rate_Result */
        $result = $this->_getRateResult();

        $methods = $this->_extractServiceMethods($serviceResults);

        /** @var $serviceMethod Mage_Shipping_Model_Carrier_Service_Method */
        foreach ($methods as $serviceMethod) {
            try {
                $rateResult = $serviceMethod->createRateResultMethod();
                $result->append($rateResult);
            } catch (Exception $ex) {
                Mage::logException($ex);
            }
        }

        return $result;
    }

    /**
     * @param $ex Exception
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function createErrorRateResult($ex)
    {
        /** @var $rateResult Mage_Shipping_Model_Rate_Result */
        $rateResult = $this->_getRateResult();
        $rateResult->setError(true);
        return $rateResult;
    }

    protected function _getRateResult()
    {
        return $this->_rateResult;
    }

    protected function _extractServiceMethods($serviceResults)
    {
        $result = array();

        if (!is_array($serviceResults) || empty($serviceResults)) {
            return $result;
        }
        $shippingMethods = $serviceResults['shippingMethods'];
        if (!is_array($shippingMethods)) {
            return $result;
        }

        foreach ($shippingMethods as $method) {
            if (!is_array($method)) {
                return $result;
            }
            $result[] = $this->_getCarrierServiceMethod($method);
        }

        return $result;
    }

    protected function _getCarrierServiceMethod($method)
    {
        return Mage::getModel('Mage_Shipping_Model_Carrier_Service_Method', array('data' => $method));
    }
}
