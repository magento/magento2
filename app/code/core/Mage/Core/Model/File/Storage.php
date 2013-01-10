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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * File storage model class
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_File_Storage extends Mage_Core_Model_Abstract
{
    /**
     * Storage systems ids
     */
    const STORAGE_MEDIA_FILE_SYSTEM         = 0;
    const STORAGE_MEDIA_DATABASE            = 1;

    /**
     * Config pathes for storing storage configuration
     */
    const XML_PATH_STORAGE_MEDIA            = 'default/system/media_storage_configuration/media_storage';
    const XML_PATH_STORAGE_MEDIA_DATABASE   = 'default/system/media_storage_configuration/media_database';
    const XML_PATH_MEDIA_RESOURCE_WHITELIST = 'default/system/media_storage_configuration/allowed_resources';
    const XML_PATH_MEDIA_UPDATE_TIME        = 'system/media_storage_configuration/configuration_update_time';


    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_file_storage';

    /**
     * Show if there were errors while synchronize process
     *
     * @param  Mage_Core_Model_Abstract $sourceModel
     * @param  Mage_Core_Model_Abstract $destinationModel
     * @return bool
     */
    protected function _synchronizeHasErrors(Mage_Core_Model_Abstract $sourceModel,
        Mage_Core_Model_Abstract $destinationModel
    ) {
        if (!$sourceModel || !$destinationModel) {
            return true;
        }

        return $sourceModel->hasErrors() || $destinationModel->hasErrors();
    }

    /**
     * Return synchronize process status flag
     *
     * @return Mage_Core_Model_File_Storage_Flag
     */
    public function getSyncFlag()
    {
        return Mage::getSingleton('Mage_Core_Model_File_Storage_Flag')->loadSelf();
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
     * @return Mage_Core_Model_Abstract|bool
     */
    public function getStorageModel($storage = null, $params = array())
    {
        if (is_null($storage)) {
            $storage = Mage::helper('Mage_Core_Helper_File_Storage')->getCurrentStorageCode();
        }

        switch ($storage) {
            case self::STORAGE_MEDIA_FILE_SYSTEM:
                $model = Mage::getModel('Mage_Core_Model_File_Storage_File');
                break;
            case self::STORAGE_MEDIA_DATABASE:
                $connection = (isset($params['connection'])) ? $params['connection'] : null;
                $arguments = array('connection' => $connection);
                $model = Mage::getModel('Mage_Core_Model_File_Storage_Database',
                    array('connectionName' => $arguments));
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
     * @return Mage_Core_Model_File_Storage
     */
    public function synchronize($storage)
    {
        if (is_array($storage) && isset($storage['type'])) {
            $storageDest    = (int) $storage['type'];
            $connection     = (isset($storage['connection'])) ? $storage['connection'] : null;
            $helper         = Mage::helper('Mage_Core_Helper_File_Storage');

            // if unable to sync to internal storage from itself
            if ($storageDest == $helper->getCurrentStorageCode() && $helper->isInternalStorage()) {
                return $this;
            }

            $sourceModel        = $this->getStorageModel();
            $destinationModel   = $this->getStorageModel(
                $storageDest,
                array(
                    'connection'    => $connection,
                    'init'          => true
                )
            );

            if (!$sourceModel || !$destinationModel) {
                return $this;
            }

            $hasErrors = false;
            $flag = $this->getSyncFlag();
            $flagData = array(
                'source'                        => $sourceModel->getStorageName(),
                'destination'                   => $destinationModel->getStorageName(),
                'destination_storage_type'      => $storageDest,
                'destination_connection_name'   => (string) $destinationModel->getConfigConnectionName(),
                'has_errors'                    => false,
                'timeout_reached'               => false
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

                $flag->setFlagData($flagData)
                    ->save();

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

                $flag->setFlagData($flagData)
                    ->save();

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
    public static function getScriptConfig()
    {
        $config = array();
        $config['media_directory'] = Mage::getBaseDir('media');

        $allowedResources = (array) Mage::app()->getConfig()->getNode(self::XML_PATH_MEDIA_RESOURCE_WHITELIST);
        foreach ($allowedResources as $key => $allowedResource) {
            $config['allowed_resources'][] = $allowedResource;
        }

        $config['update_time'] = Mage::getStoreConfig(self::XML_PATH_MEDIA_UPDATE_TIME);

        return $config;
    }
}
