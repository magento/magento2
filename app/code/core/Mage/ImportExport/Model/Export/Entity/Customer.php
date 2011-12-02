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
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Export entity customer model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Export_Entity_Customer extends Mage_ImportExport_Model_Export_Entity_Abstract
{
    /**
     * Permanent column names.
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COL_EMAIL   = 'email';
    const COL_WEBSITE = '_website';
    const COL_STORE   = '_store';

    /**
     * Overriden attributes parameters.
     *
     * @var array
     */
    protected $_attributeOverrides = array(
        'created_at'                  => array('backend_type' => 'datetime'),
        'reward_update_notification'  => array('source_model' => 'Mage_Eav_Model_Entity_Attribute_Source_Boolean'),
        'reward_warning_notification' => array('source_model' => 'Mage_Eav_Model_Entity_Attribute_Source_Boolean')
    );

    /**
     * Array of attributes codes which are disabled for export.
     *
     * @var array
     */
    protected $_disabledAttrs = array('default_billing', 'default_shipping');

    /**
     * Attributes with index (not label) value.
     *
     * @var array
     */
    protected $_indexValueAttributes = array('group_id', 'website_id', 'store_id');

    /**
     * Permanent entity columns.
     *
     * @var array
     */
    protected $_permanentAttributes = array(self::COL_EMAIL, self::COL_WEBSITE, self::COL_STORE);

    /**
     * Array of pairs store ID to its code.
     *
     * @var array
     */
    protected $_storeIdToCode = array();

    /**
     * Website ID-to-code.
     *
     * @var array
     */
    protected $_websiteIdToCode = array();

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->_initAttrValues()
                ->_initStores()
                ->_initWebsites();
    }

    /**
     * Initialize website values.
     *
     * @return Mage_ImportExport_Model_Export_Entity_Customer
     */
    protected function _initWebsites()
    {
        /** @var $website Mage_Core_Model_Website */
        foreach (Mage::app()->getWebsites(true) as $website) {
            $this->_websiteIdToCode[$website->getId()] = $website->getCode();
        }
        return $this;
    }

    /**
     * Apply filter to collection and add not skipped attributes to select.
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _prepareEntityCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        // forced addition default billing and shipping addresses attributes
        return parent::_prepareEntityCollection($collection)->addAttributeToSelect(
            Mage_ImportExport_Model_Import_Entity_Customer_Address::getDefaultAddressAttrMapping()
        );
    }

    /**
     * Export process.
     *
     * @return string
     */
    public function export()
    {
        $collection     = $this->_prepareEntityCollection(
            Mage::getResourceModel('Mage_Customer_Model_Resource_Customer_Collection')
        );
        $validAttrCodes = $this->_getExportAttrCodes();
        $writer         = $this->getWriter();
        $defaultAddrMap = Mage_ImportExport_Model_Import_Entity_Customer_Address::getDefaultAddressAttrMapping();

        // prepare address data
        $addrAttributes = array();
        $addrColNames   = array();
        $customerAddrs  = array();

        foreach (Mage::getResourceModel('Mage_Customer_Model_Resource_Address_Attribute_Collection')
                    ->addSystemHiddenFilter()
                    ->addExcludeHiddenFrontendFilter() as $attribute) {
            $options  = array();
            $attrCode = $attribute->getAttributeCode();

            if ($attribute->usesSource() && 'country_id' != $attrCode) {
                foreach ($attribute->getSource()->getAllOptions(false) as $option) {
                    foreach (is_array($option['value']) ? $option['value'] : array($option) as $innerOption) {
                        if (strlen($innerOption['value'])) { // skip ' -- Please Select -- ' option
                            $options[$innerOption['value']] = $innerOption['label'];
                        }
                    }
                }
            }
            $addrAttributes[$attrCode] = $options;
            $addrColNames[] = Mage_ImportExport_Model_Import_Entity_Customer_Address::getColNameForAttrCode($attrCode);
        }

        $addresses = Mage::getResourceModel('Mage_Customer_Model_Resource_Address_Collection')
            ->addAttributeToSelect('*');
        foreach ($addresses as $address) {
            $addrRow = array();

            foreach ($addrAttributes as $attrCode => $attrValues) {
                if (null !== $address->getData($attrCode)) {
                    $value = $address->getData($attrCode);

                    if ($attrValues) {
                        $value = $attrValues[$value];
                    }
                    $column = Mage_ImportExport_Model_Import_Entity_Customer_Address::getColNameForAttrCode($attrCode);
                    $addrRow[$column] = $value;
                }
            }
            $customerAddrs[$address['parent_id']][$address->getId()] = $addrRow;
        }

        // create export file
        $writer->setHeaderCols(array_merge(
            $this->_permanentAttributes, $validAttrCodes,
            array('password'), $addrColNames,
            array_keys($defaultAddrMap)
        ));
        foreach ($collection as $itemId => $item) { // go through all customers
            $row = array();

            // go through all valid attribute codes
            foreach ($validAttrCodes as $attrCode) {
                $attrValue = $item->getData($attrCode);

                if (isset($this->_attributeValues[$attrCode])
                    && isset($this->_attributeValues[$attrCode][$attrValue])
                ) {
                    $attrValue = $this->_attributeValues[$attrCode][$attrValue];
                }
                if (null !== $attrValue) {
                    $row[$attrCode] = $attrValue;
                }
            }
            $row[self::COL_WEBSITE] = $this->_websiteIdToCode[$item['website_id']];
            $row[self::COL_STORE]   = $this->_storeIdToCode[$item['store_id']];

            // addresses injection
            $defaultAddrs = array();

            foreach ($defaultAddrMap as $colName => $addrAttrCode) {
                if (!empty($item[$addrAttrCode])) {
                    $defaultAddrs[$item[$addrAttrCode]][] = $colName;
                }
            }
            if (isset($customerAddrs[$itemId])) {
                while (($addrRow = each($customerAddrs[$itemId]))) {
                    if (isset($defaultAddrs[$addrRow['key']])) {
                        foreach ($defaultAddrs[$addrRow['key']] as $colName) {
                            $row[$colName] = 1;
                        }
                    }
                    $writer->writeRow(array_merge($row, $addrRow['value']));

                    $row = array();
                }
            } else {
                $writer->writeRow($row);
            }
        }
        return $writer->getContents();
    }

    /**
     * Clean up already loaded attribute collection.
     *
     * @param Mage_Eav_Model_Resource_Entity_Attribute_Collection $collection
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Collection
     */
    public function filterAttributeCollection(Mage_Eav_Model_Resource_Entity_Attribute_Collection $collection)
    {
        foreach (parent::filterAttributeCollection($collection) as $attribute) {
            if (!empty($this->_attributeOverrides[$attribute->getAttributeCode()])) {
                $data = $this->_attributeOverrides[$attribute->getAttributeCode()];

                if (isset($data['options_method']) && method_exists($this, $data['options_method'])) {
                    $data['filter_options'] = $this->$data['options_method']();
                }
                $attribute->addData($data);
            }
        }
        return $collection;
    }

    /**
     * Entity attributes collection getter.
     *
     * @return Mage_Customer_Model_Resource_Attribute_Collection
     */
    public function getAttributeCollection()
    {
        return Mage::getResourceModel('Mage_Customer_Model_Resource_Attribute_Collection');
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'customer';
    }
}
