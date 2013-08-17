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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Export customer address entity model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 * @todo refactor in the scope of https://wiki.magento.com/display/MAGE2/Technical+Debt+%28Team-Donetsk-B%29
 *
 * @method Mage_Customer_Model_Resource_Address_Attribute_Collection getAttributeCollection() getAttributeCollection()
 */
class Mage_ImportExport_Model_Export_Entity_Eav_Customer_Address
    extends Mage_ImportExport_Model_Export_Entity_EavAbstract
{
    /**#@+
     * Permanent column names
     *
     * Names that begins with underscore is not an attribute.
     * This name convention is for to avoid interference with same attribute name.
     */
    const COLUMN_EMAIL      = '_email';
    const COLUMN_WEBSITE    = '_website';
    const COLUMN_ADDRESS_ID = '_entity_id';
    /**#@-*/

    /**#@+
     * Particular columns that contains of customer default addresses
     */
    const COLUMN_NAME_DEFAULT_BILLING  = '_address_default_billing_';
    const COLUMN_NAME_DEFAULT_SHIPPING = '_address_default_shipping_';
    /**#@-*/

    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = 'Mage_Customer_Model_Resource_Address_Attribute_Collection';
    /**#@-*/

    /**#@+
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = 'export/customer_page_size/address';
    /**#@-*/

    /**
     * Permanent entity columns
     *
     * @var array
     */
    protected $_permanentAttributes = array(self::COLUMN_WEBSITE, self::COLUMN_EMAIL, self::COLUMN_ADDRESS_ID);

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
     * Customers whose addresses are exported
     *
     * @var Mage_Customer_Model_Resource_Customer_Collection
     */
    protected $_customerCollection;

    /**
     * Customer addresses collection
     *
     * @var Mage_Customer_Model_Resource_Address_Collection
     */
    protected $_addressCollection;

    /**
     * Customers whose address are exported
     *
     * @var Mage_ImportExport_Model_Export_Entity_Eav_Customer
     */
    protected $_customerEntity;

    /**
     * Existing customers information. In form of:
     *
     * [customer e-mail] => array(
     *    [website id 1] => customer_id 1,
     *    [website id 2] => customer_id 2,
     *           ...       =>     ...      ,
     *    [website id n] => customer_id n,
     * )
     *
     * @var array
     */
    protected $_customers = array();

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        parent::__construct($data);

        $this->_customerCollection = isset($data['customer_collection']) ? $data['customer_collection']
            : Mage::getResourceModel('Mage_Customer_Model_Resource_Customer_Collection');

        $this->_customerEntity = isset($data['customer_entity']) ? $data['customer_entity']
            : Mage::getModel('Mage_ImportExport_Model_Export_Entity_Eav_Customer');

        $this->_addressCollection = isset($data['address_collection']) ? $data['address_collection']
            : Mage::getResourceModel('Mage_Customer_Model_Resource_Address_Collection');

        $this->_initWebsites(true);
        $this->setFileName($this->getEntityTypeCode());
    }

    /**
     * Initialize existent customers data
     *
     * @return Mage_ImportExport_Model_Export_Entity_Eav_Customer_Address
     */
    protected function _initCustomers()
    {
        if (empty($this->_customers)) {
            // add customer default addresses column name to customer attribute mapping array
            $this->_customerCollection->addAttributeToSelect(self::$_defaultAddressAttributeMapping);
            // filter customer collection
            $this->_customerCollection = $this->_customerEntity->filterEntityCollection($this->_customerCollection);

            $customers = array();
            $addCustomer = function (Mage_Customer_Model_Customer $customer) use (&$customers) {
                $customers[$customer->getId()] = $customer->getData();
            };

            $this->_byPagesIterator->iterate($this->_customerCollection, $this->_pageSize, array($addCustomer));
            $this->_customers = $customers;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _getHeaderColumns()
    {
        return array_merge(
            $this->_permanentAttributes,
            $this->_getExportAttributeCodes(),
            array_keys(self::$_defaultAddressAttributeMapping)
        );
    }

    /**
     * Get customers collection
     *
     * @return Mage_Customer_Model_Resource_Address_Collection
     */
    protected function _getEntityCollection()
    {
        return $this->_addressCollection;
    }

    /**
     * Export process
     *
     * @return string
     */
    public function export()
    {
        // skip and filter by customer address attributes
        $this->_prepareEntityCollection($this->_getEntityCollection());
        $this->_getEntityCollection()->setCustomerFilter(array_keys($this->_customers));

        // prepare headers
        $this->getWriter()->setHeaderCols($this->_getHeaderColumns());

        $this->_exportCollectionByPages($this->_getEntityCollection());

        return $this->getWriter()->getContents();
    }

    /**
     * Export given customer address data plus related customer data (required for import)
     *
     * @param Mage_Customer_Model_Address $item
     * @return string
     */
    public function exportItem($item)
    {
        $row = $this->_addAttributeValuesToRow($item);

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = $this->_customers[$item->getParentId()];

        // Fill row with default address attributes values
        foreach (self::$_defaultAddressAttributeMapping as $columnName => $attributeCode) {
            if (!empty($customer[$attributeCode]) && ($customer[$attributeCode] == $item->getId())) {
                $row[$columnName] = 1;
            }
        }

        // Unique key
        $row[self::COLUMN_ADDRESS_ID] = $item['entity_id'];
        $row[self::COLUMN_EMAIL]      = $customer['email'];
        $row[self::COLUMN_WEBSITE]    = $this->_websiteIdToCode[$customer['website_id']];

        $this->getWriter()
            ->writeRow($row);
    }

    /**
     * Set parameters (push filters from post into export customer model)
     *
     * @param array $parameters
     * @return Mage_ImportExport_Model_Export_Entity_Eav_Customer_Address
     */
    public function setParameters(array $parameters)
    {
        //  push filters from post into export customer model
        $this->_customerEntity->setParameters($parameters);
        $this->_initCustomers();

        return parent::setParameters($parameters);
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
}
