<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model;

use Exception;
use Magento\Config\Model\Config\Loader as ConfigLoader;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker as ConfigSettingChecker;
use Magento\Config\Model\Config\Structure as ConfigStructure;
use Magento\Config\Model\Config\Structure\Element\Group as GroupElement;
use Magento\Config\Model\Config\Structure\Element\Field as FieldElement;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory as ConfigValueFactory;
use Magento\Framework\App\Config\ValueInterface as ConfigValueInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Transaction;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Framework\Simplexml\Element as SimplexmlElement;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Store\Model\ScopeTypeNormalizer;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Backend config model
 *
 * Used to save configuration
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 * @method string getSection()
 * @method void setSection(string $section)
 * @method string getWebsite()
 * @method void setWebsite(string $website)
 * @method string getStore()
 * @method void setStore(string $store)
 * @method string getScope()
 * @method void setScope(string $scope)
 * @method int getScopeId()
 * @method void setScopeId(int $scopeId)
 * @method string getScopeCode()
 * @method void setScopeCode(string $scopeCode)
 */
class Config extends DataObject
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
     * @var EventManagerInterface
     */
    protected $_eventManager;

    /**
     * System configuration structure
     *
     * @var ConfigStructure
     */
    protected $_configStructure;

    /**
     * Application config
     *
     * @var ScopeConfigInterface
     */
    protected $_appConfig;

    /**
     * Global factory
     *
     * @var ScopeConfigInterface
     */
    protected $_objectFactory;

    /**
     * DB transaction factory
     *
     * @var TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * Config data loader
     *
     * @var ConfigLoader
     */
    protected $_configLoader;

    /**
     * Config data factory
     *
     * @var ConfigValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ConfigSettingChecker
     */
    private $configSettingChecker;

    /**
     * @var ScopeResolverPool
     */
    private $scopeResolverPool;

    /**
     * @var ScopeTypeNormalizer
     */
    private $scopeTypeNormalizer;

    /**
     * @var PoisonPillPutInterface
     */
    private $pillPut;

    /**
     * @param ReinitableConfigInterface $config
     * @param EventManagerInterface $eventManager
     * @param ConfigStructure $configStructure
     * @param TransactionFactory $transactionFactory
     * @param ConfigLoader $configLoader
     * @param ConfigValueFactory $configValueFactory
     * @param StoreManagerInterface $storeManager
     * @param ConfigSettingChecker|null $configSettingChecker
     * @param array $data
     * @param ScopeResolverPool|null $scopeResolverPool
     * @param ScopeTypeNormalizer|null $scopeTypeNormalizer
     * @param PoisonPillPutInterface|null $pillPut
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ReinitableConfigInterface $config,
        EventManagerInterface $eventManager,
        ConfigStructure $configStructure,
        TransactionFactory $transactionFactory,
        ConfigLoader $configLoader,
        ConfigValueFactory $configValueFactory,
        StoreManagerInterface $storeManager,
        ConfigSettingChecker $configSettingChecker = null,
        array $data = [],
        ScopeResolverPool $scopeResolverPool = null,
        ScopeTypeNormalizer $scopeTypeNormalizer = null,
        PoisonPillPutInterface $pillPut = null
    ) {
        parent::__construct($data);
        $this->_eventManager = $eventManager;
        $this->_configStructure = $configStructure;
        $this->_transactionFactory = $transactionFactory;
        $this->_appConfig = $config;
        $this->_configLoader = $configLoader;
        $this->_configValueFactory = $configValueFactory;
        $this->_storeManager = $storeManager;
        $this->configSettingChecker = $configSettingChecker
            ?? ObjectManager::getInstance()->get(ConfigSettingChecker::class);
        $this->scopeResolverPool = $scopeResolverPool
            ?? ObjectManager::getInstance()->get(ScopeResolverPool::class);
        $this->scopeTypeNormalizer = $scopeTypeNormalizer
            ?? ObjectManager::getInstance()->get(ScopeTypeNormalizer::class);
        $this->pillPut = $pillPut
            ?? ObjectManager::getInstance()->get(PoisonPillPutInterface::class);
    }

    /**
     * Save config section
     *
     * Require set: section, website, store and groups
     *
     * @throws Exception
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

        /**
         * Reload config to make sure config data is consistent with the database at this point.
         */
        $this->_appConfig->reinit();
        $oldConfig = $this->_getConfig(true);

        /** @var Transaction $deleteTransaction */
        $deleteTransaction = $this->_transactionFactory->create();
        /** @var Transaction $saveTransaction */
        $saveTransaction = $this->_transactionFactory->create();

        $changedPaths = [];
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

            $changedPaths[] = $this->getChangedPaths($sectionId, $groupId, $groupData, $oldConfig, $extraOldGroups);
        }
        $changedPaths = array_merge([], ...$changedPaths);

        try {
            $deleteTransaction->delete();
            $saveTransaction->save();

            // re-init configuration
            $this->_appConfig->reinit();

            // website and store codes can be used in event implementation, so set them as well
            $this->_eventManager->dispatch(
                "admin_system_config_changed_section_{$this->getSection()}",
                [
                    'website' => $this->getWebsite(),
                    'store' => $this->getStore(),
                    'changed_paths' => $changedPaths,
                ]
            );
        } catch (Exception $e) {
            // re-init configuration
            $this->_appConfig->reinit();
            throw $e;
        }

        $this->pillPut->put();

        return $this;
    }

    /**
     * Map field name if they were cloned
     *
     * @param GroupElement $group
     * @param string $fieldId
     * @return string
     */
    private function getOriginalFieldId(GroupElement $group, string $fieldId): string
    {
        if ($group->shouldCloneFields()) {
            $cloneModel = $group->getCloneModel();

            /** @var FieldElement $field */
            foreach ($group->getChildren() as $field) {
                foreach ($cloneModel->getPrefixes() as $prefix) {
                    if ($prefix['field'] . $field->getId() === $fieldId) {
                        $fieldId = $field->getId();
                        break(2);
                    }
                }
            }
        }

        return $fieldId;
    }

    /**
     * Get field object
     *
     * @param string $sectionId
     * @param string $groupId
     * @param string $fieldId
     * @return FieldElement
     */
    private function getField(string $sectionId, string $groupId, string $fieldId): FieldElement
    {
        /** @var GroupElement $group */
        $group = $this->_configStructure->getElement($sectionId . '/' . $groupId);
        $fieldPath = $group->getPath() . '/' . $this->getOriginalFieldId($group, $fieldId);
        $field = $this->_configStructure->getElement($fieldPath);

        return $field;
    }

    /**
     * Get field path
     *
     * @param FieldElement $field
     * @param string $fieldId Need for support of clone_field feature
     * @param array $oldConfig Need for compatibility with _processGroup()
     * @param array $extraOldGroups Need for compatibility with _processGroup()
     * @return string
     */
    private function getFieldPath(
        FieldElement $field,
        string $fieldId,
        array &$oldConfig,
        array &$extraOldGroups
    ): string {
        $path = $field->getGroupPath() . '/' . $fieldId;

        /**
         * Look for custom defined field path
         */
        $configPath = $field->getConfigPath();
        if ($configPath && strrpos($configPath, '/') > 0) {
            // Extend old data with specified section group
            $configGroupPath = substr($configPath, 0, strrpos($configPath, '/'));
            if (!isset($extraOldGroups[$configGroupPath])) {
                $oldConfig = $this->extendConfig($configGroupPath, true, $oldConfig);
                $extraOldGroups[$configGroupPath] = true;
            }
            $path = $configPath;
        }

        return $path;
    }

    /**
     * Check is config value changed
     *
     * @param array $oldConfig
     * @param string $path
     * @param array $fieldData
     * @return bool
     */
    private function isValueChanged(array $oldConfig, string $path, array $fieldData): bool
    {
        if (isset($oldConfig[$path]['value'])) {
            $result = !isset($fieldData['value']) || $oldConfig[$path]['value'] !== $fieldData['value'];
        } else {
            $result = empty($fieldData['inherit']);
        }

        return $result;
    }

    /**
     * Get changed paths
     *
     * @param string $sectionId
     * @param string $groupId
     * @param array $groupData
     * @param array $oldConfig
     * @param array $extraOldGroups
     * @return array
     */
    private function getChangedPaths(
        string $sectionId,
        string $groupId,
        array $groupData,
        array &$oldConfig,
        array &$extraOldGroups
    ): array {
        $changedPaths = [];

        if (isset($groupData['fields'])) {
            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $field = $this->getField($sectionId, $groupId, $fieldId);
                $path = $this->getFieldPath($field, $fieldId, $oldConfig, $extraOldGroups);
                if ($this->isValueChanged($oldConfig, $path, $fieldData)) {
                    $changedPaths[] = [$path];
                }
            }
        }

        if (isset($groupData['groups'])) {
            $subSectionId = $sectionId . '/' . $groupId;
            foreach ($groupData['groups'] as $subGroupId => $subGroupData) {
                $subGroupChangedPaths = $this->getChangedPaths(
                    $subSectionId,
                    $subGroupId,
                    $subGroupData,
                    $oldConfig,
                    $extraOldGroups
                );
                $changedPaths[] = $subGroupChangedPaths;
            }
        }

        return \array_merge([], ...$changedPaths);
    }

    /**
     * Process group data
     *
     * @param string $groupId
     * @param array $groupData
     * @param array $groups
     * @param string $sectionPath
     * @param array $extraOldGroups
     * @param array $oldConfig
     * @param Transaction $saveTransaction
     * @param Transaction $deleteTransaction
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _processGroup(
        $groupId,
        array $groupData,
        array $groups,
        $sectionPath,
        array &$extraOldGroups,
        array &$oldConfig,
        Transaction $saveTransaction,
        Transaction $deleteTransaction
    ) {
        $groupPath = $sectionPath . '/' . $groupId;

        if (isset($groupData['fields'])) {
            /** @var GroupElement $group */
            $group = $this->_configStructure->getElement($groupPath);

            // set value for group field entry by fieldname
            // use extra memory
            $fieldsetData = [];
            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $fieldsetData[$fieldId] = $fieldData['value'] ?? null;
            }

            foreach ($groupData['fields'] as $fieldId => $fieldData) {
                $isReadOnly = $this->configSettingChecker->isReadOnly(
                    $groupPath . '/' . $fieldId,
                    $this->getScope(),
                    $this->getScopeCode()
                );

                if ($isReadOnly) {
                    continue;
                }

                $field = $this->getField($sectionPath, $groupId, $fieldId);
                /** @var ConfigValueInterface $backendModel */
                $backendModel = $field->hasBackendModel()
                    ? $field->getBackendModel()
                    : $this->_configValueFactory->create();

                if (!isset($fieldData['value'])) {
                    $fieldData['value'] = null;
                }

                if ($field->getType() == 'multiline' && is_array($fieldData['value'])) {
                    $fieldData['value'] = trim(implode(PHP_EOL, $fieldData['value']));
                }

                $data = [
                    'field' => $fieldId,
                    'groups' => $groups,
                    'group_id' => $group->getId(),
                    'scope' => $this->getScope(),
                    'scope_id' => $this->getScopeId(),
                    'scope_code' => $this->getScopeCode(),
                    'field_config' => $field->getData(),
                    'fieldset_data' => $fieldsetData,
                ];
                $backendModel->addData($data);
                $this->_checkSingleStoreMode($field, $backendModel);

                $path = $this->getFieldPath($field, $fieldId, $oldConfig, $extraOldGroups);
                $backendModel->setPath($path)->setValue($fieldData['value']);

                $inherit = !empty($fieldData['inherit']);
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
        if ($this->_configData === null) {
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
        $path = $path !== null ? trim($path) : '';
        if ($path === '') {
            throw new \UnexpectedValueException('Path must not be empty');
        }

        $pathParts = explode('/', $path);
        $keyDepth = count($pathParts);
        if ($keyDepth < 3) {
            throw new \UnexpectedValueException(
                'Minimal depth of configuration is 3. Your configuration depth is ' . $keyDepth
            );
        }

        $section = array_shift($pathParts);
        $this->setData('section', $section);

        $data = [
            'fields' => [
                array_pop($pathParts) => ['value' => $value],
            ],
        ];
        while ($pathParts) {
            $data = [
                'groups' => [
                    array_pop($pathParts) => $data,
                ],
            ];
        }
        $groups = array_replace_recursive((array) $this->getData('groups'), $data['groups']);
        $this->setData('groups', $groups);
    }

    /**
     * Set scope data
     *
     * @return void
     */
    private function initScope()
    {
        if ($this->getSection() === null) {
            $this->setSection('');
        }

        $scope = $this->retrieveScope();
        $this->setScope($this->scopeTypeNormalizer->normalize($scope->getScopeType()));
        $this->setScopeCode($scope->getCode());
        $this->setScopeId($scope->getId());

        if ($this->getWebsite() === null) {
            $this->setWebsite(StoreScopeInterface::SCOPE_WEBSITES === $this->getScope() ? $scope->getId() : '');
        }
        if ($this->getStore() === null) {
            $this->setStore(StoreScopeInterface::SCOPE_STORES === $this->getScope() ? $scope->getId() : '');
        }
    }

    /**
     * Retrieve scope from initial data
     *
     * @return ScopeInterface
     */
    private function retrieveScope(): ScopeInterface
    {
        $scopeType = $this->getScope();
        if (!$scopeType) {
            switch (true) {
                case $this->getStore():
                    $scopeType = StoreScopeInterface::SCOPE_STORES;
                    $scopeIdentifier = $this->getStore();
                    break;
                case $this->getWebsite():
                    $scopeType = StoreScopeInterface::SCOPE_WEBSITES;
                    $scopeIdentifier = $this->getWebsite();
                    break;
                default:
                    $scopeType = ScopeInterface::SCOPE_DEFAULT;
                    $scopeIdentifier = null;
                    break;
            }
        } else {
            switch (true) {
                case $this->getScopeId() !== null:
                    $scopeIdentifier = $this->getScopeId();
                    break;
                case $this->getScopeCode() !== null:
                    $scopeIdentifier = $this->getScopeCode();
                    break;
                case $this->getStore() !== null:
                    $scopeIdentifier = $this->getStore();
                    break;
                case $this->getWebsite() !== null:
                    $scopeIdentifier = $this->getWebsite();
                    break;
                default:
                    $scopeIdentifier = null;
                    break;
            }
        }
        $scope = $this->scopeResolverPool->get($scopeType)
            ->getScope($scopeIdentifier);

        return $scope;
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
     * @param FieldElement $fieldConfig
     * @param ConfigValueInterface $dataObject
     * @return void
     */
    protected function _checkSingleStoreMode(
        FieldElement $fieldConfig,
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
     * @param null|bool $inherit
     * @param null|array $configData
     * @return SimplexmlElement
     */
    public function getConfigDataValue($path, &$inherit = null, $configData = null)
    {
        $this->load();
        if ($configData === null) {
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
