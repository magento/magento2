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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\File;

use Magento\Framework\App\Filesystem;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Storage
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
    protected $_eventPrefix = 'core_file_storage';

    /**
     * Core file storage
     *
     * @var \Magento\Core\Helper\File\Storage
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
     * @var \Magento\Core\Model\File\Storage\Flag
     */
    protected $_fileFlag;

    /**
     * File factory
     *
     * @var \Magento\Core\Model\File\Storage\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Core\Model\File\Storage\DatabaseFactory
     */
    protected $_databaseFactory;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\App\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Core\Helper\File\Storage $coreFileStorage
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Core\Model\File\Storage\Flag $fileFlag
     * @param \Magento\Core\Model\File\Storage\FileFactory $fileFactory
     * @param \Magento\Core\Model\File\Storage\DatabaseFactory $databaseFactory
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Core\Helper\File\Storage $coreFileStorage,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Core\Model\File\Storage\Flag $fileFlag,
        \Magento\Core\Model\File\Storage\FileFactory $fileFactory,
        \Magento\Core\Model\File\Storage\DatabaseFactory $databaseFactory,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_coreFileStorage = $coreFileStorage;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreConfig = $coreConfig;
        $this->_fileFlag = $fileFlag;
        $this->_fileFactory = $fileFactory;
        $this->_databaseFactory = $databaseFactory;
        $this->filesystem = $filesystem;
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
     * @return \Magento\Core\Model\File\Storage\Flag
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
    public function getStorageModel($storage = null, $params = array())
    {
        if (is_null($storage)) {
            $storage = $this->_coreFileStorage->getCurrentStorageCode();
        }

        switch ($storage) {
            case self::STORAGE_MEDIA_FILE_SYSTEM:
                $model = $this->_fileFactory->create();
                break;
            case self::STORAGE_MEDIA_DATABASE:
                $connection = isset($params['connection']) ? $params['connection'] : null;
                $model = $this->_databaseFactory->create(array('connectionName' => $connection));
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
                array('connection' => $connection, 'init' => true)
            );

            if (!$sourceModel || !$destinationModel) {
                return $this;
            }

            $hasErrors = false;
            $flag = $this->getSyncFlag();
            $flagData = array(
                'source' => $sourceModel->getStorageName(),
                'destination' => $destinationModel->getStorageName(),
                'destination_storage_type' => $storageDest,
                'destination_connection_name' => (string)$destinationModel->getConnectionName(),
                'has_errors' => false,
                'timeout_reached' => false
            );
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
        $config = array();
        $config['media_directory'] = $this->filesystem->getPath(Filesystem::MEDIA_DIR);

        $allowedResources = $this->_coreConfig->getValue(self::XML_PATH_MEDIA_RESOURCE_WHITELIST, 'default');
        foreach ($allowedResources as $allowedResource) {
            $config['allowed_resources'][] = $allowedResource;
        }

        $config['update_time'] = $this->_scopeConfig->getValue(self::XML_PATH_MEDIA_UPDATE_TIME, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $config;
    }
}
