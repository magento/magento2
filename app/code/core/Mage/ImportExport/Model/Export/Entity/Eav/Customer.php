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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Export entity customer model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method Mage_Customer_Model_Resource_Attribute_Collection getAttributeCollection() getAttributeCollection()
 */
class Mage_ImportExport_Model_Export_Entity_Eav_Customer
    extends Mage_ImportExport_Model_Export_Entity_EavAbstract
{
    /**#@+
     * Permanent column names.
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_EMAIL   = 'email';
    const COLUMN_WEBSITE = '_website';
    const COLUMN_STORE   = '_store';
    /**#@-*/

    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = 'Mage_Customer_Model_Resource_Attribute_Collection';
    /**#@-*/

    /**#@+
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = 'export/customer_page_size/customer';
    /**#@-*/

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
     * Array of attributes codes which are disabled for export
     *
     * @var array
     */
    protected $_disabledAttributes = array('default_billing', 'default_shipping');

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
    protected $_permanentAttributes = array(self::COLUMN_EMAIL, self::COLUMN_WEBSITE, self::COLUMN_STORE);

    /**
     * Customers whose data is exported
     *
     * @var Mage_Customer_Model_Resource_Customer_Collection
     */
    protected $_customerCollection;

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

        $this->_initAttributeValues()
            ->_initStores()
            ->_initWebsites(true);
    }

    /**
     * Export process.
     *
     * @return string
     */
    public function export()
    {
        $collection = $this->_prepareEntityCollection($this->_customerCollection);
        $validAttributeCodes = $this->_getExportAttributeCodes();
        $writer = $this->getWriter();

        // create export file
        $writer->setHeaderCols(array_merge($this->_permanentAttributes, $validAttributeCodes, array('password')));
        $this->_exportCollectionByPages($collection);

        return $writer->getContents();
    }

    /**
     * Export given customer data
     *
     * @param Mage_Customer_Model_Customer $item
     * @return string
     */
    public function exportItem($item)
    {
        $row = $this->_addAttributeValuesToRow($item);
        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
        $row[self::COLUMN_STORE]   = $this->_storeIdToCode[$item->getStoreId()];

        $this->getWriter()
            ->writeRow($row);
    }

    /**
     * Clean up already loaded attribute collection.
     *
     * @param Varien_Data_Collection $collection
     * @return Varien_Data_Collection
     */
    public function filterAttributeCollection(Varien_Data_Collection $collection)
    {
        /** @var $attribute Mage_Customer_Model_Attribute */
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
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->getAttributeCollection()->getEntityTypeCode();
    }

    /**
     * Retrieve list of overridden attributes
     *
     * @return array
     */
    public function getOverriddenAttributes()
    {
        return $this->_attributeOverrides;
    }
}
