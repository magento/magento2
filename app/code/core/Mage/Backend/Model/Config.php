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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend config model
 * Used to save configuration
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_Backend_Model_Config extends Varien_Object
{
    /**
     * Event dispatcher
     *
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * Reader that retreives structure of configuration edit form from storage
     *
     * @var Mage_Backend_Model_Config_Structure_Reader
     */
    protected $_structureReader;

    /**
     * Application config
     *
     * @var Mage_Core_Model_Config
     */
    protected $_appConfig;

    /**
     * Global factory
     *
     * @var Mage_Core_Model_Config
     */
    protected $_objectFactory;

    /**
     * TransactionFactory
     *
     * @var Mage_Core_Model_Resource_Transaction_Factory
     */
    protected $_transactionFactory;

    /**
     * Global Application
     *
     * @var Mage_Core_Model_App
     */
    protected $_application;

    public function __construct(array $data = array())
    {
        $this->_eventManager = isset($data['eventManager']) ?
            $data['eventManager'] :
            Mage::getSingleton('Mage_Core_Model_Event_Manager');

        $this->_structureReader = isset($data['structureReader']) ?
            $data['structureReader'] :
            Mage::getSingleton('Mage_Backend_Model_Config_Structure_Reader');

        $this->_transactionFactory = isset($data['transactionFactory']) ?
            $data['transactionFactory'] :
            Mage::getSingleton('Mage_Core_Model_Resource_Transaction_Factory');

        $this->_objectFactory = isset($data['objectFactory']) ?
            $data['objectFactory'] :
            Mage::getConfig();

        $this->_appConfig = isset($data['applicationConfig']) ?
            $data['applicationConfig'] :
            Mage::getConfig();

        $this->_application = isset($data['application']) ? $data['application'] : Mage::app();

        parent::__construct($data);
    }

    /**
     * Save config section
     * Require set: section, website, store and groups
     *
     * @return Mage_Backend_Model_Config
     */
    public function save()
    {
        $this->_validate();
        $this->_getScope();

        $this->_eventManager->dispatch('model_config_data_save_before', array('object' => $this));

        $section = $this->getSection();
        $website = $this->getWebsite();
        $store   = $this->getStore();
        $groups  = $this->getGroups();
        $scope   = $this->getScope();
        $scopeId = $this->getScopeId();

        if (empty($groups)) {
            return $this;
        }

        $sections = $this->_structureReader->getConfiguration()->getSections();

        $oldConfig = $this->_getConfig(true);

        $deleteTransaction = $this->_transactionFactory->create();
        /* @var $deleteTransaction Mage_Core_Model_Resource_Transaction */
        $saveTransaction = $this->_transactionFactory->create();
        /* @var $saveTransaction Mage_Core_Model_Resource_Transaction */

        // Extends for old config data
        $oldConfigAdditionalGroups = array();

        foreach ($groups as $group => $groupData) {

            /**
             * Map field names if they were cloned
             */
            $groupConfig = $sections[$section]['groups'][$group];

            if ($clonedFields = (isset($groupConfig['clone_fields']) && !empty($groupConfig['clone_fields']))) {
                if (isset($groupConfig['clone_model']) && $groupConfig['clone_model']) {
                    $cloneModel = $this->_objectFactory->getModelInstance((string)$groupConfig['clone_model']);
                } else {
                    Mage::throwException('Config form fieldset clone model required to be able to clone fields');
                }
                $mappedFields = array();

                if (isset($groupConfig['fields'])) {
                    $fieldsConfig = $groupConfig['fields'];

                    foreach ($fieldsConfig as $field => $node) {
                        foreach ($cloneModel->getPrefixes() as $prefix) {
                            $mappedFields[$prefix['field'] . (string)$field] = (string)$field;
                        }
                    }
                }
            }
            // set value for group field entry by fieldname
            // use extra memory
            $fieldsetData = array();
            foreach ($groupData['fields'] as $field => $fieldData) {
                $fieldsetData[$field] = (is_array($fieldData) && isset($fieldData['value']))
                    ? $fieldData['value'] : null;
            }

            foreach ($groupData['fields'] as $field => $fieldData) {
                /**
                 * Get field backend model
                 */
                if (isset($groupConfig['fields'][$field]['backend_model'])) {
                    $backendClass = $groupConfig['fields'][$field]['backend_model'];
                } else if ($clonedFields &&
                    isset($mappedFields[$field]) &&
                    isset($groupConfig['fields'][$mappedFields[$field]]['backend_model'])
                ) {
                    $backendClass = $groupConfig['fields'][$mappedFields[$field]]['backend_model'];
                } else {
                    $backendClass = 'Mage_Core_Model_Config_Data';
                }

                /* @var $dataObject Mage_Core_Model_Config_Data */
                $dataObject = $this->_objectFactory->getModelInstance($backendClass);
                if (!$dataObject instanceof Mage_Core_Model_Config_Data) {
                    Mage::throwException('Invalid config field backend model: ' . $backendClass);
                }

                if (isset($groupConfig['fields'][$field])) {
                    $fieldConfig = $groupConfig['fields'][$field];
                } else if ($clonedFields && isset($mappedFields[$field])) {
                    $fieldConfig = $groupConfig['fields'][$mappedFields[$field]];
                }

                $dataObject
                    ->setField($field)
                    ->setGroups($groups)
                    ->setGroupId($group)
                    ->setStoreCode($store)
                    ->setWebsiteCode($website)
                    ->setScope($scope)
                    ->setScopeId($scopeId)
                    ->setFieldConfig($fieldConfig)
                    ->setFieldsetData($fieldsetData);

                $this->_checkSingleStoreMode($fieldConfig, $dataObject);

                if (!isset($fieldData['value'])) {
                    $fieldData['value'] = null;
                }

                $path = $section . '/' . $group . '/' . $field;

                /**
                 * Look for custom defined field path
                 */
                if ($fieldConfig && isset($fieldConfig['config_path'])) {
                    $configPath = (string)$fieldConfig['config_path'];
                    if (!empty($configPath) && strrpos($configPath, '/') > 0) {
                        // Extend old data with specified section group
                        $groupPath = substr($configPath, 0, strrpos($configPath, '/'));
                        if (!isset($oldConfigAdditionalGroups[$groupPath])) {
                            $oldConfig = $this->extendConfig($groupPath, true, $oldConfig);
                            $oldConfigAdditionalGroups[$groupPath] = true;
                        }
                        $path = $configPath;
                    }
                }

                $inherit = !empty($fieldData['inherit']);

                $dataObject->setPath($path)
                    ->setValue($fieldData['value']);

                if (isset($oldConfig[$path])) {
                    $dataObject->setConfigId($oldConfig[$path]['config_id']);

                    /**
                     * Delete config data if inherit
                     */
                    if (!$inherit) {
                        $saveTransaction->addObject($dataObject);
                    } else {
                        $deleteTransaction->addObject($dataObject);
                    }
                } elseif (!$inherit) {
                    $dataObject->unsConfigId();
                    $saveTransaction->addObject($dataObject);
                }
            }

        }

        $deleteTransaction->delete();
        $saveTransaction->save();

        return $this;
    }

    /**
     * Load config data for section
     *
     * @return array
     */
    public function load()
    {
        $this->_validate();
        $this->_getScope();

        return $this->_getConfig(false);
    }

    /**
     * Extend config data with additional config data by specified path
     *
     * @param string $path Config path prefix
     * @param bool $full Simple config structure or not
     * @param array $oldConfig Config data to extend
     * @return array
     */
    public function extendConfig($path, $full = true, $oldConfig = array())
    {
        $extended = $this->_getPathConfig($path, $full);
        if (is_array($oldConfig) && !empty($oldConfig)) {
            return $oldConfig + $extended;
        }
        return $extended;
    }

    /**
     * Validate isset required parametrs
     *
     */
    protected function _validate()
    {
        if (is_null($this->getSection())) {
            $this->setSection('');
        }
        if (is_null($this->getWebsite())) {
            $this->setWebsite('');
        }
        if (is_null($this->getStore())) {
            $this->setStore('');
        }
    }

    /**
     * Get scope name and scopeId
     *
     */
    protected function _getScope()
    {
        if ($this->getStore()) {
            $scope   = 'stores';
            $scopeId = (int) $this->_appConfig->getNode('stores/' . $this->getStore() . '/system/store/id');
        } elseif ($this->getWebsite()) {
            $scope   = 'websites';
            $scopeId = (int) $this->_appConfig->getNode('websites/' . $this->getWebsite() . '/system/website/id');
        } else {
            $scope   = 'default';
            $scopeId = 0;
        }
        $this->setScope($scope);
        $this->setScopeId($scopeId);
    }

    /**
     * Return formatted config data for current section
     *
     * @param bool $full Simple config structure or not
     * @return array
     */
    protected function _getConfig($full = true)
    {
        return $this->_getPathConfig($this->getSection(), $full);
    }

    /**
     * Return formatted config data for specified path prefix
     *
     * @param string $path Config path prefix
     * @param bool $full Simple config structure or not
     * @return array
     */
    protected function _getPathConfig($path, $full = true)
    {
        $configDataCollection = $this->_objectFactory->getModelInstance('Mage_Core_Model_Config_Data')
            ->getCollection()
            ->addScopeFilter($this->getScope(), $this->getScopeId(), $path);

        $config = array();
        foreach ($configDataCollection as $data) {
            if ($full) {
                $config[$data->getPath()] = array(
                    'path'      => $data->getPath(),
                    'value'     => $data->getValue(),
                    'config_id' => $data->getConfigId()
                );
            }
            else {
                $config[$data->getPath()] = $data->getValue();
            }
        }
        return $config;
    }

    /**
     * Set correct scope if isSingleStoreMode = true
     *
     * @param array $fieldConfig
     * @param Mage_Core_Model_Config_Data $dataObject
     */
    protected function _checkSingleStoreMode($fieldConfig, $dataObject)
    {
        $isSingleStoreMode = $this->_application->isSingleStoreMode();
        if (!$isSingleStoreMode) {
            return;
        }
        if (!isset($fieldConfig['showInDefault']) || !(int)$fieldConfig['showInDefault']) {
            $websites = $this->_application->getWebsites();
            $singleStoreWebsite = array_shift($websites);
            $dataObject->setScope('websites');
            $dataObject->setWebsiteCode($singleStoreWebsite->getCode());
            $dataObject->setScopeId($singleStoreWebsite->getId());
        }
    }
}
