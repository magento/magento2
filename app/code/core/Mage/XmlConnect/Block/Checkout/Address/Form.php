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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer address form xml renderer for onepage checkout
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Checkout_Address_Form extends Mage_Core_Block_Template
{
    /**
     * Render customer address form xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $xmlModel Mage_XmlConnect_Model_Simplexml_Element */
        $xmlModel = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
            array('data' => '<form></form>'));
        $xmlModel->addAttribute('name', 'address_form');
        $xmlModel->addAttribute('method', 'post');

        $addressType = $this->getType();
        if (!$addressType) {
            $addressType = 'billing';
        }

        $isAllowedGuestCheckout = Mage::helper('Mage_Checkout_Helper_Data')->isAllowedGuestCheckout(
            Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote()
        );

        $countries = $this->_getCountryOptions();

        $xmlModel->addField($addressType . '[firstname]', 'text', array(
            'label'     => $this->__('First Name'),
            'required'  => 'true',
            'value'     => ''
        ));

        $xmlModel->addField($addressType . '[lastname]', 'text', array(
            'label'     => $this->__('Last Name'),
            'required'  => 'true',
            'value'     => ''
        ));

        $xmlModel->addField($addressType . '[company]', 'text', array(
            'label'     => $this->__('Company'),
            'required'  => 'true',
            'value'     => ''
        ));

        if ($isAllowedGuestCheckout && !Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()
            && $addressType == 'billing'
        ) {
            $emailField = $xmlModel->addField($addressType . '[email]', 'text', array(
                'label'     => $this->__('Email Address'),
                'required'  => 'true',
                'value'     => ''
            ));
            $emailValidator = $emailField->addChild('validators')->addChild('validator');
            $emailValidator->addAttribute('type', 'email');
            $emailValidator->addAttribute('message', $this->__('Wrong email format'));
        }

        $xmlModel->addField($addressType . '[street][]', 'text', array(
            'label'     => $this->__('Address'),
            'required'  => 'true',
            'value'     => ''
        ));

        $xmlModel->addField($addressType . '[street][]', 'text', array(
             'label'     => $this->__('Address 2'),
             'value'     => ''
        ));

        $xmlModel->addField($addressType . '[city]', 'text', array(
            'label'     => $this->__('City'),
            'required'  => 'true',
            'value'     => ''
        ));

        $countryOptionsXml = $xmlModel->addField($addressType . '[country_id]', 'select', array(
            'label'     => $this->__('Country'),
            'required'  => 'true',
            'value'     => ''
        ))->addChild('values');

        foreach ($countries as $data) {
            $regions = array();

            if ($data['value']) {
                $regions = $this->_getRegionOptions($data['value']);
            }

            $regionStr = (!empty($regions) ? 'region_id' : 'region');

            $countryXml = $countryOptionsXml->addCustomChild('item', null, array('relation' => $regionStr));
            $countryXml->addCustomChild('label', (string)$data['label']);
            $countryXml->addCustomChild('value', (string)$data['value']);
            if (!empty($regions)) {
                $regionXml = $countryXml->addChild('regions');
                foreach ($regions as $_data) {
                    $regionItemXml = $regionXml->addChild('region_item');
                    $regionItemXml->addCustomChild('label', (string)$_data['label']);
                    $regionItemXml->addCustomChild('value', (string)$_data['value']);
                }
            }
        }

        $xmlModel->addField($addressType . '[region]', 'text', array(
            'label' => $this->__('State/Province'),
            'value' => ''
        ));

        $xmlModel->addField($addressType . '[region_id]', 'select', array(
            'label'     => $this->__('State/Province'),
            'required'  => 'true',
            'value'     => ''
        ));

        $xmlModel->addField($addressType . '[postcode]', 'text', array(
            'label'     => $this->__('Zip/Postal Code'),
            'required'  => 'true',
            'value'     => ''
        ));

        $xmlModel->addField($addressType . '[telephone]', 'text', array(
            'label'     => $this->__('Telephone'),
            'required'  => 'true',
            'value'     => ''
        ));

        $xmlModel->addField($addressType . '[fax]', 'text', array(
            'label' => $this->__('Fax'),
            'value' => ''
        ));

        $xmlModel->addField($addressType . '[save_in_address_book]', 'checkbox', array(
            'label' => $this->__('Save in address book'),
        ));

        return $xmlModel->asNiceXml();
    }

    /**
     * Retrieve regions by country
     *
     * @param string $countryId
     * @return array
     */
    protected function _getRegionOptions($countryId)
    {
        $cacheKey = 'DIRECTORY_REGION_SELECT_STORE' . Mage::app()->getStore()->getId() . $countryId;
        $cache = Mage::app()->loadCache($cacheKey);
        if (Mage::app()->useCache('config') && $cache) {
            $options = unserialize($cache);
        } else {
            $collection = Mage::getModel('Mage_Directory_Model_Region')->getResourceCollection()->addCountryFilter($countryId)
                ->load();
            $options = $collection->toOptionArray();
            if (Mage::app()->useCache('config')) {
                Mage::app()->saveCache(serialize($options), $cacheKey, array('config'));
            }
        }
        return $options;
    }

    /**
     * Retrieve countries
     *
     * @return array
     */
    protected function _getCountryOptions()
    {
        $cacheKey = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
        $cache = Mage::app()->loadCache($cacheKey);
        if (Mage::app()->useCache('config') && $cache) {
            $options = unserialize($cache);
        } else {
            /** @var $collection Mage_Directory_Model_Resource_Country_Collection */
            $collection = Mage::getModel('Mage_Directory_Model_Country')->getResourceCollection()->loadByStore();
            $options = $collection->toOptionArray(false);
            if (Mage::app()->useCache('config')) {
                Mage::app()->saveCache(serialize($options), $cacheKey, array('config'));
            }
        }
        return $options;
    }
}
