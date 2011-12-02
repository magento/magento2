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
 * @package     Mage_Payment
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment information model
 *
 * @category   Mage
 * @package    Mage_Payment
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Payment_Model_Info extends Mage_Core_Model_Abstract
{
    /**
     * Additional information container
     *
     * @var array
     */
    protected $_additionalInformation = -1;

    /**
     * Retrieve data
     *
     * @param   string $key
     * @param   mixed $index
     * @return unknown
     */
    public function getData($key='', $index=null)
    {
        if ('cc_number'===$key) {
            if (empty($this->_data['cc_number']) && !empty($this->_data['cc_number_enc'])) {
                $this->_data['cc_number'] = $this->decrypt($this->getCcNumberEnc());
            }
        }
        if ('cc_cid'===$key) {
            if (empty($this->_data['cc_cid']) && !empty($this->_data['cc_cid_enc'])) {
                $this->_data['cc_cid'] = $this->decrypt($this->getCcCidEnc());
            }
        }
        return parent::getData($key, $index);
    }

    /**
     * Retrieve payment method model object
     *
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function getMethodInstance()
    {
        if (!$this->hasMethodInstance()) {
            if ($method = $this->getMethod()) {
                if ($instance = Mage::helper('Mage_Payment_Helper_Data')->getMethodInstance($this->getMethod())) {
                    $instance->setInfoInstance($this);
                    $this->setMethodInstance($instance);
                    return $instance;
                }
            }
        } else {
            return $this->_getData('method_instance');
        }
        Mage::throwException(Mage::helper('Mage_Payment_Helper_Data')->__('Cannot retrieve payment method instance.'));
    }

    /**
     * Encrypt data
     *
     * @param   string $data
     * @return  string
     */
    public function encrypt($data)
    {
        if ($data) {
            return Mage::helper('Mage_Core_Helper_Data')->encrypt($data);
        }
        return $data;
    }

    /**
     * Decrypt data
     *
     * @param   string $data
     * @return  string
     */
    public function decrypt($data)
    {
        if ($data) {
            return Mage::helper('Mage_Core_Helper_Data')->decrypt($data);
        }
        return $data;
    }

    /**
     * Additional information setter
     * Updates data inside the 'additional_information' array
     * or all 'additional_information' if key is data array
     *
     * @param string|array $key
     * @param mixed $value
     * @return Mage_Payment_Model_Info
     * @throws Mage_Core_Exception
     */
    public function setAdditionalInformation($key, $value = null)
    {
        if (is_object($value)) {
            Mage::throwException(Mage::helper('Mage_Sales_Helper_Data')->__('Payment disallow storing objects.'));
        }
        $this->_initAdditionalInformation();
        if (is_array($key) && is_null($value)) {
            $this->_additionalInformation = $key;
        } else {
            $this->_additionalInformation[$key] = $value;
        }
        return $this->setData('additional_information', $this->_additionalInformation);
    }

    /**
     * Getter for entire additional_information value or one of its element by key
     *
     * @param string $key
     * @return array|null|mixed
     */
    public function getAdditionalInformation($key = null)
    {
        $this->_initAdditionalInformation();
        if (null === $key) {
            return $this->_additionalInformation;
        }
        return isset($this->_additionalInformation[$key]) ? $this->_additionalInformation[$key] : null;
    }

    /**
     * Unsetter for entire additional_information value or one of its element by key
     *
     * @param string $key
     * @return Mage_Payment_Model_Info
     */
    public function unsAdditionalInformation($key = null)
    {
        if ($key && isset($this->_additionalInformation[$key])) {
            unset($this->_additionalInformation[$key]);
            return $this->setData('additional_information', $this->_additionalInformation);
        }
        $this->_additionalInformation = -1;
        return $this->unsetData('additional_information');
    }

    /**
     * Check whether there is additional information by specified key
     *
     * @param $key
     * @return bool
     */
    public function hasAdditionalInformation($key = null)
    {
        $this->_initAdditionalInformation();
        return null === $key
            ? !empty($this->_additionalInformation)
            : array_key_exists($key, $this->_additionalInformation);
    }

    /**
     * Make sure _additionalInformation is an array
     */
    protected function _initAdditionalInformation()
    {
        if (-1 === $this->_additionalInformation) {
            $this->_additionalInformation = $this->_getData('additional_information');
        }
        if (null === $this->_additionalInformation) {
            $this->_additionalInformation = array();
        }
    }
}
