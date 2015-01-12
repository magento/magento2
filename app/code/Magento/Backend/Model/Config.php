<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

/**
 * Backend config model
 * Used to save configuration
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Config extends \Magento\Framework\Object
{
    /**
     * Config data for sections
     *
     * @var array
     */
    protected $_configData;

    /**
     * Event dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * System configuration structure
     *
     * @var \Magento\Backend\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * Application config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_appConfig;

    /**
     * Global factory
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_objectFactory;

    /**
     * TransactionFactory
     *
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * Config data loader
     *
     * @var \Magento\Backend\Model\Config\Loader
     */
    protected $_configLoader;

    /**
     * Config data factory
     *
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Backend\Model\Config\Structure $configStructure
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Backend\Model\Config\Loader $configLoader
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Backend\Model\Config\Structure $configStructure,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Backend\Model\Config\Loader $configLoader,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_eventManager = $eventManager;
        $this->_configStructure = $configStructure;
        $this->_transactionFactory = $transactionFactory;
        $this->_appConfig = $config;
        $this->_configLoader = $configLoader;
        $this->_configValueFactory = $configValueFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * Save config section
     * Require set: section, website, store and groups
     *
     * @throws \Exception
     * @return $this
     */
    public function save()
    {
        $this->initScope();

        $sectionId = $this->getSection();
        $groups = $this->getGroups();
        if (empty($groups)) {
            return $this;
        }

        $oldConfig = $this->_getConfig(true);

        $deleteTransaction = $this->_transactionFactory->create();
        /* @var $deleteTransaction \Magento\Framework\DB\Transaction */
        $saveTransaction = $this->_transactionFactory->create();
        /* @var $saveTransaction \Magento\Framework\DB\Transaction */

        // Extends for old config data
        $extraOldGroups = [];

        foreach ($groups as $groupId => $groupData) {
            $this->_processGroup(
                $groupId,
                $groupData,
                $groups,
                $sectionId,
                $extraOldGroups,
                $oldConfig,
                $saveTransaction,
                $deleteTransaction
            );
        }

        try {
            $deleteTransaction->delete();
            $saveTransaction->save();

            // re-init configuration
            $this->_appConfig->reinit();
            $this->_storeManager->reinitStores();

            // website and store codes can be used in event implementation, so set them as well
            $this->_eventManager->dispatch(
                "admin_system_config_changed_section_{$this->getSection()}",
                ['website' => $this->getWebsite(), 'store' => $this->getStore()]
            );
        } catch (\Exception $e) {
            // re-init configuration
            $this->_appConfig->reinit();
            $this->_storeManager->reinitStores();
            throw $e;
        }

        return $this;
    }

    /**
     * Process group data
     *
     * @param string $groupId
     * @param array $groupData
     * @param array $groups
     * @param string $sectionPath
     * @param array &$extraOldGroups
     * @param array &$oldConfig
     * @param \Magento\Framework\DB\Transaction $saveTransaction
     * @param \Magento\Framework\DB\Transaction $deleteTransaction
     * @return void
     */
    protected function _processGroup(
        $groupId,
        array $groupData,
        array $groups,
        $sectionPath,
        array &$extraOldGroups,
        array &$oldConfig,
        \Magento\Framework\DB\Transaction $saveTransaction,
        \Magento\Framework\DB\Transaction $deleteTransaction
    ) {
        $groupPath = $sectionPath . '/' . $groupId;
        $scope = $this->getScope();
        $scopeId = $this->getScopeId();
        $scopeCode = $this->getScopeCode();
        /**
         *
         * Map field names if they were cloned
         */
        /** @var $group \Magento\Backend\Model\Config\Structure\Element\Group */
        $group = $this->_configStructure->getElement($groupPath);


        // set value for group field entry by fieldname
        // use extra memory
        $fieldsetData = [];
        if (isset($groupData['fields'])) {
            if ($group->shouldCloneFields()) {
                $cloneModel = $group->getCloneModel();
                $mappedFields = [];

                /** @var $field \Magento\Backend\Model\Config\Structure\Element\Field */
                foreach ($group->getChildren() as $field) {
                    foreach ($cloneModel->getPrefixes() as $prefix) {
                        $mappedFields[$prefix['field'] . $field->getId()] = $field->getId();
                    }
                }
            }
            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $fieldsetData[$fieldId] = is_array(
                    $fieldData
                ) && isset(
                    $fieldData['value']
                ) ? $fieldData['value'] : null;
            }

            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $originalFieldId = $fieldId;
                if ($group->shouldCloneFields() && isset($mappedFields[$fieldId])) {
                    $originalFieldId = $mappedFields[$fieldId];
                }
                /** @var $field \Magento\Backend\Model\Config\Structure\Element\Field */
                $field = $this->_configStructure->getElement($groupPath . '/' . $originalFieldId);

                /** @var \Magento\Framework\App\Config\ValueInterface $backendModel */
                $backendModel = $field->hasBackendModel() ? $field
                    ->getBackendModel() : $this
                    ->_configValueFactory
                    ->create();

                $data = [
                    'field' => $fieldId,
                    'groups' => $groups,
                    'group_id' => $group->getId(),
                    'scope' => $scope,
                    'scope_id' => $scopeId,
                    'scope_code' => $scopeCode,
                    'field_config' => $field->getData(),
                    'fieldset_data' => $fieldsetData
                ];
                $backendModel->addData($data);

                $this->_checkSingleStoreMode($field, $backendModel);

                if (false == isset($fieldData['value'])) {
                    $fieldData['value'] = null;
                }

                $path = $field->getGroupPath() . '/' . $fieldId;
                /**
                 * Look for custom defined field path
                 */
                if ($field && $field->getConfigPath()) {
                    $configPath = $field->getConfigPath();
                    if (!empty($configPath) && strrpos($configPath, '/') > 0) {
                        // Extend old data with specified section group
                        $configGroupPath = substr($configPath, 0, strrpos($configPath, '/'));
                        if (!isset($extraOldGroups[$configGroupPath])) {
                            $oldConfig = $this->extendConfig($configGroupPath, true, $oldConfig);
                            $extraOldGroups[$configGroupPath] = true;
                        }
                        $path = $configPath;
                    }
                }

                $inherit = !empty($fieldData['inherit']);

                $backendModel->setPath($path)->setValue($fieldData['value']);

                if (isset($oldConfig[$path])) {
                    $backendModel->setConfigId($oldConfig[$path]['config_id']);

                    /**
                     * Delete config data if inherit
                     */
                    if (!$inherit) {
                        $saveTransaction->addObject($backendModel);
                    } else {
                        $deleteTransaction->addObject($backendModel);
                    }
                } elseif (!$inherit) {
                    $backendModel->unsConfigId();
                    $saveTransaction->addObject($backendModel);
                }
            }
        }

        if (isset($groupData['groups'])) {
            foreach ($groupData['groups'] as $subGroupId => $subGroupData) {
                $this->_processGroup(
                    $subGroupId,
                    $subGroupData,
                    $groups,
                    $groupPath,
                    $extraOldGroups,
                    $oldConfig,
                    $saveTransaction,
                    $deleteTransaction
                );
            }
        }
    }

    /**
     * Load config data for section
     *
     * @return array
     */
    public function load()
    {
        if (is_null($this->_configData)) {
            $this->initScope();
            $this->_configData = $this->_getConfig(false);
        }
        return $this->_configData;
    }

    /**
     * Extend config data with additional config data by specified path
     *
     * @param string $path Config path prefix
     * @param bool $full Simple config structure or not
     * @param array $oldConfig Config data to extend
     * @return array
     */
    public function extendConfig($path, $full = true, $oldConfig = [])
    {
        $extended = $this->_configLoader->getConfigByPath($path, $this->getScope(), $this->getScopeId(), $full);
        if (is_array($oldConfig) && !empty($oldConfig)) {
            return $oldConfig + $extended;
        }
        return $extended;
    }

    /**
     * Add data by path section/group/field
     *
     * @param string $path
     * @param mixed $value
     * @return void
     * @throws \UnexpectedValueException
     */
    public function setDataByPath($path, $value)
    {
        $path = trim($path);
        if ($path === '') {
            throw new \UnexpectedValueException('Path must not be empty');
        }
        $pathParts = explode('/', $path);
        $keyDepth = count($pathParts);
        if ($keyDepth !== 3) {
            throw new \UnexpectedValueException(
                "Allowed depth of configuration is 3 (<section>/<group>/<field>). Your configuration depth is "
                . $keyDepth . " for path '$path'"
            );
        }
        $data = [
            'section' => $pathParts[0],
            'groups' => [
                $pathParts[1] => [
                    'fields' => [
                        $pathParts[2] => ['value' => $value],
                    ],
                ],
            ],
        ];
        $this->addData($data);
    }

    /**
     * Get scope name and scopeId
     * @todo refactor to scope resolver
     * @return void
     */
    private function initScope()
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


        if ($this->getStore()) {
            $scope = 'stores';
            $store = $this->_storeManager->getStore($this->getStore());
            $scopeId = (int)$store->getId();
            $scopeCode = $store->getCode();
        } elseif ($this->getWebsite()) {
            $scope = 'websites';
            $website = $this->_storeManager->getWebsite($this->getWebsite());
            $scopeId = (int)$website->getId();
            $scopeCode = $website->getCode();
        } else {
            $scope = 'default';
            $scopeId = 0;
            $scopeCode = '';
        }
        $this->setScope($scope);
        $this->setScopeId($scopeId);
        $this->setScopeCode($scopeCode);
    }

    /**
     * Return formatted config data for current section
     *
     * @param bool $full Simple config structure or not
     * @return array
     */
    protected function _getConfig($full = true)
    {
        return $this->_configLoader->getConfigByPath(
            $this->getSection(),
            $this->getScope(),
            $this->getScopeId(),
            $full
        );
    }

    /**
     * Set correct scope if isSingleStoreMode = true
     *
     * @param \Magento\Backend\Model\Config\Structure\Element\Field $fieldConfig
     * @param \Magento\Framework\App\Config\ValueInterface $dataObject
     * @return void
     */
    protected function _checkSingleStoreMode(
        \Magento\Backend\Model\Config\Structure\Element\Field $fieldConfig,
        $dataObject
    ) {
        $isSingleStoreMode = $this->_storeManager->isSingleStoreMode();
        if (!$isSingleStoreMode) {
            return;
        }
        if (!$fieldConfig->showInDefault()) {
            $websites = $this->_storeManager->getWebsites();
            $singleStoreWebsite = array_shift($websites);
            $dataObject->setScope('websites');
            $dataObject->setWebsiteCode($singleStoreWebsite->getCode());
            $dataObject->setScopeCode($singleStoreWebsite->getCode());
            $dataObject->setScopeId($singleStoreWebsite->getId());
        }
    }

    /**
     * Get config data value
     *
     * @param string $path
     * @param null|bool &$inherit
     * @param null|array $configData
     * @return \Magento\Framework\Simplexml\Element
     */
    public function getConfigDataValue($path, &$inherit = null, $configData = null)
    {
        $this->load();
        if (is_null($configData)) {
            $configData = $this->_configData;
        }
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = $this->_appConfig->getValue($path, $this->getScope(), $this->getScopeCode());
            $inherit = true;
        }

        return $data;
    }
}
