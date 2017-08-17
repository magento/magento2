<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Shipping table rates
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\OfflineShipping\Model\ResourceModel\Carrier;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Import;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQuery;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQueryFactory;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 */
class Tablerate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Import table rates website ID
     *
     * @var int
     */
    protected $_importWebsiteId = 0;

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = [];

    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $_importedRows = 0;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash = [];

    /**
     * Array of countries keyed by iso2 code
     *
     * @var array
     */
    protected $_importIso2Countries;

    /**
     * Array of countries keyed by iso3 code
     *
     * @var array
     */
    protected $_importIso3Countries;

    /**
     * Associative array of countries and regions
     * [country_id][region_code] = region_id
     *
     * @var array
     */
    protected $_importRegions;

    /**
     * Import Table Rate condition name
     *
     * @var string
     */
    protected $_importConditionName;

    /**
     * Array of condition full names
     *
     * @var array
     */
    protected $_conditionFullNames = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 100.1.0
     */
    protected $coreConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 100.1.0
     */
    protected $logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 100.1.0
     */
    protected $storeManager;

    /**
     * @var \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate
     * @since 100.1.0
     */
    protected $carrierTablerate;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     * @since 100.1.0
     */
    protected $filesystem;

    /**
     * @var Import
     */
    private $import;

    /**
     * @var RateQueryFactory
     */
    private $rateQueryFactory;

    /**
     * Tablerate constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\OfflineShipping\Model\Carrier\Tablerate $carrierTablerate
     * @param Filesystem $filesystem
     * @param RateQueryFactory $rateQueryFactory
     * @param Import $import
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\OfflineShipping\Model\Carrier\Tablerate $carrierTablerate,
        \Magento\Framework\Filesystem $filesystem,
        Import $import,
        RateQueryFactory $rateQueryFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->coreConfig = $coreConfig;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->carrierTablerate = $carrierTablerate;
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->rateQueryFactory = $rateQueryFactory;
    }

    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('shipping_tablerate', 'pk');
    }

    /**
     * Return table rate array or false by rate request
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return array|bool
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable());
        /** @var RateQuery $rateQuery */
        $rateQuery = $this->rateQueryFactory->create(['request' => $request]);

        $rateQuery->prepareSelect($select);
        $bindings = $rateQuery->getBindings();

        $result = $connection->fetchRow($select, $bindings);
        // Normalize destination zip code
        if ($result && $result['dest_zip'] == '*') {
            $result['dest_zip'] = '';
        }

        return $result;
    }

    /**
     * @param array $condition
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function deleteByCondition(array $condition)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        $connection->delete($this->getMainTable(), $condition);
        $connection->commit();
        return $this;
    }

    /**
     * @param array $fields
     * @param array $values
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function importData(array $fields, array $values)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            if (count($fields) && count($values)) {
                $this->getConnection()->insertArray($this->getMainTable(), $fields, $values);
                $this->_importedRows += count($values);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollback();
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to import data'), $e);
        } catch (\Exception $e) {
            $connection->rollback();
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while importing table rates.')
            );
        }
        $connection->commit();
    }

    /**
     * Upload table rate file and import data from it
     *
     * @param \Magento\Framework\DataObject $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate
     * @todo: this method should be refactored as soon as updated design will be provided
     * @see https://wiki.corp.x.com/display/MCOMS/Magento+Filesystem+Decisions
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function uploadAndImport(\Magento\Framework\DataObject $object)
    {
        /**
         * @var \Magento\Framework\App\Config\Value $object
         */
        if (empty($_FILES['groups']['tmp_name']['tablerate']['fields']['import']['value'])) {
            return $this;
        }
        $filePath = $_FILES['groups']['tmp_name']['tablerate']['fields']['import']['value'];

        $websiteId = $this->storeManager->getWebsite($object->getScopeId())->getId();
        $conditionName = $this->getConditionName($object);

        $file = $this->getCsvFile($filePath);
        try {
            // delete old data by website and condition name
            $condition = [
                'website_id = ?' => $websiteId,
                'condition_name = ?' => $conditionName,
            ];
            $this->deleteByCondition($condition);

            $columns = $this->import->getColumns();
            $conditionFullName = $this->_getConditionFullName($conditionName);
            foreach ($this->import->getData($file, $websiteId, $conditionName, $conditionFullName) as $bunch) {
                $this->importData($columns, $bunch);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while importing table rates.')
            );
        } finally {
            $file->close();
        }

        if ($this->import->hasErrors()) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->import->getErrors())
            );
            throw new \Magento\Framework\Exception\LocalizedException($error);
        }
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return mixed|string
     * @since 100.1.0
     */
    public function getConditionName(\Magento\Framework\DataObject $object)
    {
        if ($object->getData('groups/tablerate/fields/condition_name/inherit') == '1') {
            $conditionName = (string)$this->coreConfig->getValue('carriers/tablerate/condition_name', 'default');
        } else {
            $conditionName = $object->getData('groups/tablerate/fields/condition_name/value');
        }
        return $conditionName;
    }

    /**
     * @param string $filePath
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     */
    private function getCsvFile($filePath)
    {
        $tmpDirectory = $this->filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $path = $tmpDirectory->getRelativePath($filePath);
        return $tmpDirectory->openFile($path);
    }

    /**
     * Return import condition full name by condition name code
     *
     * @param string $conditionName
     * @return string
     */
    protected function _getConditionFullName($conditionName)
    {
        if (!isset($this->_conditionFullNames[$conditionName])) {
            $name = $this->carrierTablerate->getCode('condition_name_short', $conditionName);
            $this->_conditionFullNames[$conditionName] = $name;
        }

        return $this->_conditionFullNames[$conditionName];
    }

    /**
     * Save import data batch
     *
     * @param array $data
     * @return \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = [
                'website_id',
                'dest_country_id',
                'dest_region_id',
                'dest_zip',
                'condition_name',
                'condition_value',
                'price',
            ];
            $this->getConnection()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }
}
