<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Model\Import;

use Magento\CustomerImportExport\Model\Resource\Import\Customer\Storage;

/**
 * Import entity abstract customer model
 */
abstract class AbstractCustomer extends \Magento\ImportExport\Model\Import\Entity\AbstractEav
{
    /**#@+
     * Permanent column names
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_WEBSITE = '_website';

    const COLUMN_EMAIL = '_email';

    /**#@-*/

    /**#@+
     * Error codes
     */
    const ERROR_WEBSITE_IS_EMPTY = 'websiteIsEmpty';

    const ERROR_EMAIL_IS_EMPTY = 'emailIsEmpty';

    const ERROR_INVALID_WEBSITE = 'invalidWebsite';

    const ERROR_INVALID_EMAIL = 'invalidEmail';

    const ERROR_VALUE_IS_REQUIRED = 'valueIsRequired';

    const ERROR_CUSTOMER_NOT_FOUND = 'customerNotFound';

    /**#@-*/

    /**
     * Array of attribute codes which will be ignored in validation and import procedures.
     * For example, when entity attribute has own validation and import procedures
     * or just to deny this attribute processing.
     *
     * @var string[]
     */
    protected $_ignoredAttributes = ['website_id', 'store_id', 'default_billing', 'default_shipping'];

    /**
     * Customer collection wrapper
     *
     * @var Storage
     */
    protected $_customerStorage;

    /**
     * @var \Magento\CustomerImportExport\Model\Resource\Import\Customer\StorageFactory
     */
    protected $_storageFactory;

    /**
     * {@inheritdoc}
     */
    protected $masterAttributeCode = '_email';

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\ImportExport\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\CustomerImportExport\Model\Resource\Import\Customer\StorageFactory $storageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Framework\Stdlib\String $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\ImportExport\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\App\Resource $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\CustomerImportExport\Model\Resource\Import\Customer\StorageFactory $storageFactory,
        array $data = []
    ) {
        $this->_storageFactory = $storageFactory;
        parent::__construct(
            $coreData,
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $storeManager,
            $collectionFactory,
            $eavConfig,
            $data
        );

        $this->addMessageTemplate(self::ERROR_WEBSITE_IS_EMPTY, __('Website is not specified'));
        $this->addMessageTemplate(self::ERROR_EMAIL_IS_EMPTY, __('E-mail is not specified'));
        $this->addMessageTemplate(self::ERROR_INVALID_WEBSITE, __("Invalid value in website column"));
        $this->addMessageTemplate(self::ERROR_INVALID_EMAIL, __('E-mail is invalid'));
        $this->addMessageTemplate(self::ERROR_VALUE_IS_REQUIRED, __("Required attribute '%s' has an empty value"));
        $this->addMessageTemplate(
            self::ERROR_CUSTOMER_NOT_FOUND,
            __("Customer with such email and website code doesn't exist")
        );

        $this->_initCustomers($data)->_initWebsites(true);
    }

    /**
     * Initialize existent customers data
     *
     * @param array $data
     * @return $this
     */
    protected function _initCustomers(array $data)
    {
        if (!isset($data['page_size'])) {
            $data['page_size'] = $this->_pageSize;
        }
        $this->_customerStorage = isset(
            $data['customer_storage']
        ) ? $data['customer_storage'] : $this->_storageFactory->create(
            ['data' => $data]
        );

        return $this;
    }

    /**
     * Get customer id if customer is present in database
     *
     * @param string $email
     * @param string $websiteCode
     * @return bool|int
     */
    protected function _getCustomerId($email, $websiteCode)
    {
        $email = strtolower(trim($email));
        if (isset($this->_websiteCodeToId[$websiteCode])) {
            $websiteId = $this->_websiteCodeToId[$websiteCode];
            return $this->_customerStorage->getCustomerId($email, $websiteId);
        }

        return false;
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        if (isset($this->_validatedRows[$rowNumber])) {
            // check that row is already validated
            return !isset($this->_invalidRows[$rowNumber]);
        }
        $this->_validatedRows[$rowNumber] = true;
        $this->_processedEntitiesCount++;
        if ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE) {
            $this->_validateRowForUpdate($rowData, $rowNumber);
        } elseif ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            $this->_validateRowForDelete($rowData, $rowNumber);
        }

        return !isset($this->_invalidRows[$rowNumber]);
    }

    /**
     * Validate data row for add/update behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return null
     */
    abstract protected function _validateRowForUpdate(array $rowData, $rowNumber);

    /**
     * Validate data row for delete behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return null
     */
    abstract protected function _validateRowForDelete(array $rowData, $rowNumber);

    /**
     * General check of unique key
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _checkUniqueKey(array $rowData, $rowNumber)
    {
        if (empty($rowData[static::COLUMN_WEBSITE])) {
            $this->addRowError(static::ERROR_WEBSITE_IS_EMPTY, $rowNumber, static::COLUMN_WEBSITE);
        } elseif (empty($rowData[static::COLUMN_EMAIL])) {
            $this->addRowError(static::ERROR_EMAIL_IS_EMPTY, $rowNumber, static::COLUMN_EMAIL);
        } else {
            $email = strtolower($rowData[static::COLUMN_EMAIL]);
            $website = $rowData[static::COLUMN_WEBSITE];

            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                $this->addRowError(static::ERROR_INVALID_EMAIL, $rowNumber, static::COLUMN_EMAIL);
            } elseif (!isset($this->_websiteCodeToId[$website])) {
                $this->addRowError(static::ERROR_INVALID_WEBSITE, $rowNumber, static::COLUMN_WEBSITE);
            }
        }
        return !isset($this->_invalidRows[$rowNumber]);
    }

    /**
     * Get customer storage
     *
     * @return Storage
     */
    public function getCustomerStorage()
    {
        return $this->_customerStorage;
    }
}
