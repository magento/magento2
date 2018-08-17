<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Source;

use Magento\Store\Model\Store;

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
    protected $_configVariables = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_configVariables = [
            [
                'value' => Store::XML_PATH_UNSECURE_BASE_URL,
                'label' => __('Base Unsecure URL'),
            ],
            ['value' => Store::XML_PATH_SECURE_BASE_URL, 'label' => __('Base Secure URL')],
            ['value' => 'trans_email/ident_general/name', 'label' => __('General Contact Name')],
            ['value' => 'trans_email/ident_general/email', 'label' => __('General Contact Email')],
            ['value' => 'trans_email/ident_sales/name', 'label' => __('Sales Representative Contact Name')],
            ['value' => 'trans_email/ident_sales/email', 'label' => __('Sales Representative Contact Email')],
            ['value' => 'trans_email/ident_custom1/name', 'label' => __('Custom1 Contact Name')],
            ['value' => 'trans_email/ident_custom1/email', 'label' => __('Custom1 Contact Email')],
            ['value' => 'trans_email/ident_custom2/name', 'label' => __('Custom2 Contact Name')],
            ['value' => 'trans_email/ident_custom2/email', 'label' => __('Custom2 Contact Email')],
            ['value' => 'general/store_information/name', 'label' => __('Store Name')],
            ['value' => 'general/store_information/phone', 'label' => __('Store Phone Number')],
            ['value' => 'general/store_information/hours', 'label' => __('Store Hours')],
            ['value' => 'general/store_information/country_id', 'label' => __('Country')],
            ['value' => 'general/store_information/region_id', 'label' => __('Region/State')],
            ['value' => 'general/store_information/postcode', 'label' => __('Zip/Postal Code')],
            ['value' => 'general/store_information/city', 'label' => __('City')],
            ['value' => 'general/store_information/street_line1', 'label' => __('Street Address 1')],
            ['value' => 'general/store_information/street_line2', 'label' => __('Street Address 2')],
            ['value' => 'general/store_information/merchant_vat_number', 'label' => __('VAT Number')],
        ];
    }

    /**
     * Retrieve option array of store contact variables
     *
     * @param bool $withGroup
     * @return array
     */
    public function toOptionArray($withGroup = false)
    {
        $optionArray = [];
        foreach ($this->_configVariables as $variable) {
            $optionArray[] = [
                'value' => '{{config path="' . $variable['value'] . '"}}',
                'label' => $variable['label'],
            ];
        }
        if ($withGroup && $optionArray) {
            $optionArray = ['label' => __('Store Contact Information'), 'value' => $optionArray];
        }
        return $optionArray;
    }

    /**
     * Return available config variables
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getData()
    {
        return  $this->_configVariables;
    }
}
