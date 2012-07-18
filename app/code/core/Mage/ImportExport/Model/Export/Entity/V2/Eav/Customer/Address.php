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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Export customer address entity model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer_Address
    extends Mage_ImportExport_Model_Export_Entity_V2_Eav_Abstract
{
    /**#@+
     * Permanent column names
     *
     * Names that begins with underscore is not an attribute.
     * This name convention is for to avoid interference with same attribute name.
     */
    const COL_EMAIL   = '_email';
    const COL_WEBSITE = '_website';
    const COL_ADDRESS_ID = '_entity_id';
    /**#@-*/

    /**#@+
     * Particular columns that contains of customer default addresses
     */
    const COLUMN_NAME_DEFAULT_BILLING  = '_address_default_billing_';
    const COLUMN_NAME_DEFAULT_SHIPPING = '_address_default_shipping_';
    /**#@-*/

    /**
     * Default addresses column names to appropriate customer attribute code
     *
     * @var array
     */
    protected static $_defaultAddressAttributeMapping = array(
        self::COLUMN_NAME_DEFAULT_BILLING  => 'default_billing',
        self::COLUMN_NAME_DEFAULT_SHIPPING => 'default_shipping'
    );

    /**
     * Constructor
     *
     * @return Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer_Address
     */
    public function __construct()
    {
        parent::__construct();

        $this->_permanentAttributes = array(self::COL_WEBSITE, self::COL_EMAIL, self::COL_ADDRESS_ID);

        $this->_initWebsites(true);
        $this->setFileName($this->getEntityTypeCode());
    }

    /**
     * Customer default addresses column name to customer attribute mapping array
     *
     * @static
     * @return array
     */
    public static function getDefaultAddressAttributeMapping()
    {
        return self::$_defaultAddressAttributeMapping;
    }

    /**
     * Export process
     *
     * @return string
     */
    public function export()
    {
        $writer = $this->getWriter();

        /** @var $collection Mage_Customer_Model_Resource_Address_Collection */
        $collection = Mage::getResourceModel('Mage_Customer_Model_Resource_Address_Collection');

        // Skip and Filter by customer address attributes
        $collection = $this->_prepareEntityCollection($collection);

        // Filter addresses by customer entity attributes
        /** @var $customerCollection Mage_Customer_Model_Resource_Customer_Collection */
        $customerCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Customer_Collection');

        /** @var $exportCustomer Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer */
        $exportCustomer = Mage::getModel('Mage_ImportExport_Model_Export_Entity_V2_Eav_Customer');

        //  push filters from post into export customer model
        $exportCustomer->setParameters($this->_parameters);

        $customerCollection = $exportCustomer->filterEntityCollection($customerCollection);

        // Get customer default addresses column name to customer attribute mapping array.
        $defaultAddressMap = self::getDefaultAddressAttributeMapping();
        $customerCollection->addAttributeToSelect($defaultAddressMap);

        $customers = $customerCollection->getItems();

        $collection->setCustomerFilter(array_keys($customers));

        $validAttributeCodes = $this->_getExportAttributeCodes();

        // prepare headers
        $writer->setHeaderCols(
            array_merge(
                $this->_permanentAttributes,
                $validAttributeCodes,
                array_keys($defaultAddressMap)
            )
        );

        /** @var $item Mage_Customer_Model_Address */
        foreach ($collection as $item) {
            $row = $this->_addAttributeValuesToRow($item);

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $customers[$item->getParentId()];

            // Fill row with default address attributes values
            foreach ($defaultAddressMap as $columnName => $attributeCode) {
                if (!empty($customer[$attributeCode]) && ($customer[$attributeCode] == $item->getId())) {
                    $row[$columnName] =  1;
                }
            }

            // Unique key
            $row[self::COL_ADDRESS_ID] = $item['entity_id'];
            $row[self::COL_EMAIL] = $customer->getEmail();
            $row[self::COL_WEBSITE] = $this->_websiteIdToCode[$customer->getWebsiteId()];

            $writer->writeRow($row);
        }
        return $writer->getContents();
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->getAttributeCollection()->getEntityTypeCode();
    }

    /**
     * Entity attributes collection getter.
     *
     * @return Mage_Customer_Model_Resource_Address_Attribute_Collection
     */
    public function getAttributeCollection()
    {
        return Mage::getResourceModel('Mage_Customer_Model_Resource_Address_Attribute_Collection');
    }
}
