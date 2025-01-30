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

use Magento\AsyncConfig\Setup\ConfigOptionsList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Import;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQuery;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
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
     * @var Tablerate
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
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var RequestFactory
     */
    private RequestFactory $requestFactory;

    /**
     * @var IoFile
     */
    private IoFile $ioFile;

    /**
     * Tablerate constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $coreConfig
     * @param StoreManagerInterface $storeManager
     * @param \Magento\OfflineShipping\Model\Carrier\Tablerate $carrierTablerate
     * @param Filesystem $filesystem
     * @param Import $import
     * @param RateQueryFactory $rateQueryFactory
     * @param string|null $connectionName
     * @param DeploymentConfig|null $deploymentConfig
     * @param RequestFactory|null $requestFactory
     * @param IoFile|null $ioFile
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context  $context,
        \Psr\Log\LoggerInterface                           $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Store\Model\StoreManagerInterface         $storeManager,
        \Magento\OfflineShipping\Model\Carrier\Tablerate   $carrierTablerate,
        \Magento\Framework\Filesystem                      $filesystem,
        Import                                             $import,
        RateQueryFactory                                   $rateQueryFactory,
        $connectionName = null,
        ?DeploymentConfig                                  $deploymentConfig = null,
        ?RequestFactory $requestFactory = null,
        ?IoFile $ioFile = null
    ) {
        parent::__construct($context, $connectionName);
        $this->coreConfig = $coreConfig;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->carrierTablerate = $carrierTablerate;
        $this->filesystem = $filesystem;
        $this->import = $import;
        $this->rateQueryFactory = $rateQueryFactory;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        $this->requestFactory = $requestFactory ?: ObjectManager::getInstance()->get(RequestFactory::class);
        $this->ioFile = $ioFile ?: ObjectManager::getInstance()->get(IoFile::class);
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
     * @throws LocalizedException
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
     * Delete elements from database using condition
     *
     * @param array $condition
     * @return $this
     * @throws LocalizedException
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
     * Insert import data
     *
     * @param array $fields
     * @param array $values
     * @return void
     * @throws LocalizedException
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
        } catch (LocalizedException $e) {
            $connection->rollBack();
            throw new LocalizedException(__('Unable to import data'), $e);
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->critical($e);
            throw new LocalizedException(
                __('Something went wrong while importing table rates.')
            );
        }
        $connection->commit();
    }

    /**
     * Upload table rate file and import data from it
     *
     * @param DataObject $object
     * @return Tablerate
     * @throws LocalizedException
     * @todo: this method should be refactored as soon as updated design will be provided
     */
    public function uploadAndImport(DataObject $object)
    {
        $filePath = $this->getFilePath($object);
        if (!$filePath) {
            return $this;
        }

        $websiteId = $this->storeManager->getWebsite($object->getScopeId())->getId();
        $conditionName = $this->getConditionName($object);
        $file = $this->getCsvFile($filePath);
        try {
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
            throw new LocalizedException(
                __('Something went wrong while importing table rates.')
            );
        } finally {
            $file->close();
            $this->removeFile($filePath);
        }

        if ($this->import->hasErrors()) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->import->getErrors())
            );
            throw new LocalizedException($error);
        }

        return $this;
    }

    /**
     * Extract condition name
     *
     * @param DataObject $object
     * @return mixed|string
     * @since 100.1.0
     */
    public function getConditionName(DataObject $object)
    {
        if ($object->getData('groups/tablerate/fields/condition_name/inherit') == '1') {
            $conditionName = (string)$this->coreConfig->getValue('carriers/tablerate/condition_name', 'default');
        } else {
            $conditionName = $object->getData('groups/tablerate/fields/condition_name/value');
        }
        return $conditionName;
    }

    /**
     * Determine table rate upload file path
     *
     * @param DataObject $object
     * @return string
     * @throws FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function getFilePath(DataObject $object): string
    {
        $filePath = '';

        /**
         * @var \Magento\Framework\App\Config\Value $object
         */
        if ($this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_ASYNC_CONFIG_SAVE)) {
            if (!empty($object->getFieldsetData()['import']['name']) &&
                !empty($object->getFieldsetData()['import']['full_path'])
            ) {
                $filePath = $object->getFieldsetData()['import']['full_path']
                    . $object->getFieldsetData()['import']['name'];
            }
        } else {
            $request = $this->requestFactory->create();
            $files = (array)$request->getFiles();

            if (!empty($files['groups']['tablerate']['fields']['import']['value'])) {
                $filePath = $files['groups']['tablerate']['fields']['import']['value']['tmp_name'];
            }
        }

        return $filePath;
    }

    /**
     * Open CSV file for reading
     *
     * @param string $filePath
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     * @throws FileSystemException
     */
    private function getCsvFile($filePath)
    {
        $pathInfo = $this->ioFile->getPathInfo($filePath);
        $dirName = $pathInfo['dirname'] ?? '';
        $fileName = $pathInfo['basename'] ?? '';

        $directoryRead = $this->filesystem->getDirectoryReadByPath($dirName, Filesystem\DriverPool::FILE);

        return $directoryRead->openFile($fileName);
    }

    /**
     * Remove file
     *
     * @param string $filePath
     * @return bool
     */
    private function removeFile(string $filePath): bool
    {
        $pathInfo = $this->ioFile->getPathInfo($filePath);
        $fileName = $pathInfo['basename'] ?? '';

        try {
            $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
            return $directoryWrite->delete($fileName);
        } catch (FileSystemException $exception) {
            return false;
        }
    }

    /**
     * Return import condition full name by condition name code
     *
     * @param string $conditionName
     * @return string
     * @throws LocalizedException
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
     * @return Tablerate
     * @throws LocalizedException
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
