<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\Export;

/**
 * Export entity customer model
 *
 * @method \Magento\Customer\Model\ResourceModel\Attribute\Collection getAttributeCollection() getAttributeCollection()
 */
class Customer extends \Magento\ImportExport\Model\Export\Entity\AbstractEav
{
    /**#@+
     * Permanent column names.
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_EMAIL = 'email';

    const COLUMN_WEBSITE = '_website';

    const COLUMN_STORE = '_store';

    /**#@-*/

    /**#@+
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = \Magento\Customer\Model\ResourceModel\Attribute\Collection::class;

    /**#@-*/

    /**#@+
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = 'export/customer_page_size/customer';

    /**#@-*/

    /**
     * Overridden attributes parameters.
     *
     * @var array
     */
    protected $_attributeOverrides = [
        'created_at' => ['backend_type' => 'datetime'],
        'reward_update_notification' => ['source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class],
        'reward_warning_notification' => ['source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class],
    ];

    /**
     * Array of attributes codes which are disabled for export
     *
     * @var string[]
     */
    protected $_disabledAttributes = ['default_billing', 'default_shipping'];

    /**
     * Attributes with index (not label) value.
     *
     * @var string[]
     */
    protected $_indexValueAttributes = ['group_id', 'website_id', 'store_id'];

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COLUMN_EMAIL, self::COLUMN_WEBSITE, self::COLUMN_STORE];

    /**
     * Customers whose data is exported
     *
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $_customerCollection;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerColFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerColFactory,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $localeDate,
            $eavConfig,
            $data
        );

        $this->_customerCollection = isset(
            $data['customer_collection']
        ) ? $data['customer_collection'] : $customerColFactory->create();

        $this->_initAttributeValues()->_initStores()->_initWebsites(true);
    }

    /**
     * Export process.
     *
     * @return string
     */
    public function export()
    {
        $this->_prepareEntityCollection($this->_getEntityCollection());
        $writer = $this->getWriter();

        // create export file
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($this->_getEntityCollection());

        return $writer->getContents();
    }

    /**
     * Get customers collection
     *
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected function _getEntityCollection()
    {
        return $this->_customerCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getHeaderColumns()
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();
        return array_merge($this->_permanentAttributes, $validAttributeCodes, ['password']);
    }

    /**
     * Export given customer data
     *
     * @param \Magento\Customer\Model\Customer $item
     * @return void
     */
    public function exportItem($item)
    {
        $row = $this->_addAttributeValuesToRow($item);
        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
        $row[self::COLUMN_STORE] = $this->_storeIdToCode[$item->getStoreId()];

        $this->getWriter()->writeRow($row);
    }

    /**
     * Clean up already loaded attribute collection.
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return \Magento\Framework\Data\Collection
     */
    public function filterAttributeCollection(\Magento\Framework\Data\Collection $collection)
    {
        /** @var $attribute \Magento\Customer\Model\Attribute */
        foreach (parent::filterAttributeCollection($collection) as $attribute) {
            if (!empty($this->_attributeOverrides[$attribute->getAttributeCode()])) {
                $data = $this->_attributeOverrides[$attribute->getAttributeCode()];

                if (isset($data['options_method']) && method_exists($this, $data['options_method'])) {
                    $data['filter_options'] = $this->{$data['options_method']}();
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
