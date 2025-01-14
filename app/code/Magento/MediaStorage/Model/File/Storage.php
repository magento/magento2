<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaStorage\Model\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Storage
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Storage extends AbstractModel
{
    /**
     * Storage systems ids
     */
    const STORAGE_MEDIA_FILE_SYSTEM = 0;

    const STORAGE_MEDIA_DATABASE = 1;

    /**
     * Config paths for storing storage configuration
     */
    const XML_PATH_STORAGE_MEDIA = 'system/media_storage_configuration/media_storage';

    const XML_PATH_STORAGE_MEDIA_DATABASE = 'system/media_storage_configuration/media_database';

    const XML_PATH_MEDIA_RESOURCE_WHITELIST = 'system/media_storage_configuration/allowed_resources';

    const XML_PATH_MEDIA_UPDATE_TIME = 'system/media_storage_configuration/configuration_update_time';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'media_storage_file_storage';

    /**
     * Core file storage
     *
     * @var \Magento\MediaStorage\Helper\File\Storage
     */
    protected $_coreFileStorage = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * Core file storage flag
     *
     * @var \Magento\MediaStorage\Model\File\Storage\Flag
     */
    protected $_fileFlag;

    /**
     * File factory
     *
     * @var \Magento\MediaStorage\Model\File\Storage\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\DatabaseFactory
     */
    protected $_databaseFactory;

    /**
     * @var Filesystem
     *
     * @deprecated
     */
    protected $filesystem;

    /**
     * @var Filesystem\Directory\ReadInterface
     */
    private $localMediaDirectory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\MediaStorage\Helper\File\Storage $coreFileStorage
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\MediaStorage\Model\File\Storage\Flag $fileFlag
     * @param \Magento\MediaStorage\Model\File\Storage\FileFactory $fileFactory
     * @param \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $databaseFactory
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\MediaStorage\Helper\File\Storage $coreFileStorage,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\MediaStorage\Model\File\Storage\Flag $fileFlag,
        \Magento\MediaStorage\Model\File\Storage\FileFactory $fileFactory,
        \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $databaseFactory,
        Filesystem $filesystem,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_coreFileStorage = $coreFileStorage;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreConfig = $coreConfig;
        $this->_fileFlag = $fileFlag;
        $this->_fileFactory = $fileFactory;
        $this->_databaseFactory = $databaseFactory;
        $this->filesystem = $filesystem;

        $this->localMediaDirectory = $filesystem->getDirectoryRead(
            DirectoryList::MEDIA,
            Filesystem\DriverPool::FILE
        );
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Show if there were errors while synchronize process
     *
     * @param \Magento\Framework\Model\AbstractModel $sourceModel
     * @param \Magento\Framework\Model\AbstractModel $destinationModel
     * @return bool
     */
    protected function _synchronizeHasErrors($sourceModel, $destinationModel)
    {
        if (!$sourceModel || !$destinationModel) {
            return true;
        }

        return $sourceModel->hasErrors() || $destinationModel->hasErrors();
    }

    /**
     * Return synchronize process status flag
     *
     * @return \Magento\MediaStorage\Model\File\Storage\Flag
     */
    public function getSyncFlag()
    {
        return $this->_fileFlag->loadSelf();
    }

    /**
     * Retrieve storage model
     * If storage not defined - retrieve current storage
     *
     * params = array(
     *  connection  => string,  - define connection for model if needed
     *  init        => bool     - force initialization process for storage model
     * )
     *
     * @param  int|null $storage
     * @param  array $params
     * @return AbstractModel|bool
     */
    public function getStorageModel($storage = null, $params = [])
    {
        if ($storage === null) {
            $storage = $this->_coreFileStorage->getCurrentStorageCode();
        }

        switch ($storage) {
            case self::STORAGE_MEDIA_FILE_SYSTEM:
                $model = $this->_fileFactory->create();
                break;
            case self::STORAGE_MEDIA_DATABASE:
                $connection = isset($params['connection']) ? $params['connection'] : null;
                $model = $this->_databaseFactory->create(['connectionName' => $connection]);
                break;
            default:
                return false;
        }

        if (isset($params['init']) && $params['init']) {
            $model->init();
        }

        return $model;
    }

    /**
     * Synchronize current media storage with defined
     * $storage = array(
     *  type        => int
     *  connection  => string
     * )
     *
     * @param  array $storage
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function synchronize($storage)
    {
        if (is_array($storage) && isset($storage['type'])) {
            $storageDest = (int)$storage['type'];
            $connection = isset($storage['connection']) ? $storage['connection'] : null;
            $helper = $this->_coreFileStorage;

            // if unable to sync to internal storage from itself
            if ($storageDest == $helper->getCurrentStorageCode() && $helper->isInternalStorage()) {
                return $this;
            }

            $sourceModel = $this->getStorageModel();
            $destinationModel = $this->getStorageModel(
                $storageDest,
                ['connection' => $connection, 'init' => true]
            );

            if (!$sourceModel || !$destinationModel) {
                return $this;
            }

            $hasErrors = false;
            $flag = $this->getSyncFlag();
            $flagData = [
                'source' => $sourceModel->getStorageName(),
                'destination' => $destinationModel->getStorageName(),
                'destination_storage_type' => $storageDest,
                'destination_connection_name' => (string)$destinationModel->getConnectionName(),
                'has_errors' => false,
                'timeout_reached' => false,
            ];
            $flag->setFlagData($flagData);

            $destinationModel->clear();

            $offset = 0;
            while (($dirs = $sourceModel->exportDirectories($offset)) !== false) {
                $flagData['timeout_reached'] = false;
                if (!$hasErrors) {
                    $hasErrors = $this->_synchronizeHasErrors($sourceModel, $destinationModel);
                    if ($hasErrors) {
                        $flagData['has_errors'] = true;
                    }
                }

                $flag->setFlagData($flagData)->save();

                $destinationModel->importDirectories($dirs);
                $offset += count($dirs);
            }
            unset($dirs);

            $offset = 0;
            while (($files = $sourceModel->exportFiles($offset, 1)) !== false) {
                $flagData['timeout_reached'] = false;
                if (!$hasErrors) {
                    $hasErrors = $this->_synchronizeHasErrors($sourceModel, $destinationModel);
                    if ($hasErrors) {
                        $flagData['has_errors'] = true;
                    }
                }

                $flag->setFlagData($flagData)->save();

                $destinationModel->importFiles($files);
                $offset += count($files);
            }
            unset($files);
        }

        return $this;
    }

    /**
     * Return current media directory, allowed resources for get.php script, etc.
     *
     * @return array
     */
    public function getScriptConfig()
    {
        $config = [];
        $config['media_directory'] = $this->localMediaDirectory->getAbsolutePath();

        $allowedResources = $this->_coreConfig->getValue(self::XML_PATH_MEDIA_RESOURCE_WHITELIST, 'default');
        array_walk_recursive($allowedResources, function($value, $key) use (&$resources) {
            $resources[] = $value;
        }, $resources);
        $config['allowed_resources'] = $resources;

        $config['update_time'] = $this->_scopeConfig->getValue(
            self::XML_PATH_MEDIA_UPDATE_TIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $config;
    }
}
