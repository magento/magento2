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
namespace Magento\Email\Model\Source;

/**
 * Store Contact Information source model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Variables implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Assoc array of configuration variables
     *
     * @var array
     */
    protected $_configVariables = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_configVariables = array(
            array(
                'value' => \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL,
                'label' => __('Base Unsecure URL')
            ),
            array('value' => \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, 'label' => __('Base Secure URL')),
            array('value' => 'trans_email/ident_general/name', 'label' => __('General Contact Name')),
            array('value' => 'trans_email/ident_general/email', 'label' => __('General Contact Email')),
            array('value' => 'trans_email/ident_sales/name', 'label' => __('Sales Representative Contact Name')),
            array('value' => 'trans_email/ident_sales/email', 'label' => __('Sales Representative Contact Email')),
            array('value' => 'trans_email/ident_custom1/name', 'label' => __('Custom1 Contact Name')),
            array('value' => 'trans_email/ident_custom1/email', 'label' => __('Custom1 Contact Email')),
            array('value' => 'trans_email/ident_custom2/name', 'label' => __('Custom2 Contact Name')),
            array('value' => 'trans_email/ident_custom2/email', 'label' => __('Custom2 Contact Email')),
            array('value' => 'general/store_information/name', 'label' => __('Store Name')),
            array('value' => 'general/store_information/phone', 'label' => __('Store Phone Number')),
            array('value' => 'general/store_information/country_id', 'label' => __('Country')),
            array('value' => 'general/store_information/region_id', 'label' => __('Region/State')),
            array('value' => 'general/store_information/postcode', 'label' => __('Zip/Postal Code')),
            array('value' => 'general/store_information/city', 'label' => __('City')),
            array('value' => 'general/store_information/street_line1', 'label' => __('Street Address 1')),
            array('value' => 'general/store_information/street_line2', 'label' => __('Street Address 2'))
        );
    }

    /**
     * Retrieve option array of store contact variables
     *
     * @param bool $withGroup
     * @return array
     */
    public function toOptionArray($withGroup = false)
    {
        $optionArray = array();
        foreach ($this->_configVariables as $variable) {
            $optionArray[] = array(
                'value' => '{{config path="' . $variable['value'] . '"}}',
                'label' => $variable['label']
            );
        }
        if ($withGroup && $optionArray) {
            $optionArray = array('label' => __('Store Contact Information'), 'value' => $optionArray);
        }
        return $optionArray;
    }
}
