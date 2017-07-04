<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup;

use Magento\Customer\Model\Customer;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\SetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\DataConverter\SerializedToJson;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param IndexerRegistry $indexerRegistry
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param FieldDataConverterFactory|null $fieldDataConverterFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        IndexerRegistry $indexerRegistry,
        \Magento\Eav\Model\Config $eavConfig,
        FieldDataConverterFactory $fieldDataConverterFactory = null
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavConfig = $eavConfig;

        $this->fieldDataConverterFactory = $fieldDataConverterFactory ?: ObjectManager::getInstance()->get(
            FieldDataConverterFactory::class
        );
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $this->upgradeVersionTwoZeroSix($customerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->upgradeVersionTwoZeroOne($customerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $this->upgradeVersionTwoZeroTwo($customerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->upgradeVersionTwoZeroThree($customerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $this->upgradeVersionTwoZeroFour($customerSetup);
        }

        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $this->upgradeVersionTwoZeroFive($customerSetup, $setup);
        }

        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $setup->getConnection()->delete(
                $setup->getTable('customer_form_attribute'),
                ['form_code = ?' => 'checkout_register']
            );
        }

        if (version_compare($context->getVersion(), '2.0.8', '<')) {
            $setup->getConnection()->update(
                $setup->getTable('core_config_data'),
                ['path' => \Magento\Customer\Model\Form::XML_PATH_ENABLE_AUTOCOMPLETE],
                ['path = ?' => 'general/restriction/autocomplete_on_storefront']
            );
        }

        if (version_compare($context->getVersion(), '2.0.7', '<')) {
            $this->upgradeVersionTwoZeroSeven($customerSetup);
            $this->upgradeCustomerPasswordResetlinkExpirationPeriodConfig($setup);
        }

        if (version_compare($context->getVersion(), '2.0.9', '<')) {
            $setup->getConnection()->beginTransaction();

            try {
                $this->migrateStoresAllowedCountriesToWebsite($setup);
                $setup->getConnection()->commit();
            } catch (\Exception $e) {
                $setup->getConnection()->rollBack();
                throw $e;
            }
        }
        if (version_compare($context->getVersion(), '2.0.11', '<')) {
            $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
            $fieldDataConverter->convert(
                $setup->getConnection(),
                $setup->getTable('customer_eav_attribute'),
                'attribute_id',
                'validate_rules'
            );
        }

        if (version_compare($context->getVersion(), '2.0.12', '<')) {
            $this->upgradeVersionTwoZeroTwelve($customerSetup);
        }

        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
        $this->eavConfig->clear();
        $setup->endSetup();
    }

    /**
     * Retrieve Store Manager
     *
     * @deprecated
     * @return StoreManagerInterface
     */
    private function getStoreManager()
    {
        if (!$this->storeManager) {
            $this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        }

        return $this->storeManager;
    }

    /**
     * Retrieve Allowed Countries Reader
     *
     * @deprecated
     * @return AllowedCountries
     */
    private function getAllowedCountriesReader()
    {
        if (!$this->allowedCountriesReader) {
            $this->allowedCountriesReader = ObjectManager::getInstance()->get(AllowedCountries::class);
        }

        return $this->allowedCountriesReader;
    }

    /**
     * Merge allowed countries between different scopes
     *
     * @param array $countries
     * @param array $newCountries
     * @param string $identifier
     * @return array
     */
    private function mergeAllowedCountries(array $countries, array $newCountries, $identifier)
    {
        if (!isset($countries[$identifier])) {
            $countries[$identifier] = $newCountries;
        } else {
            $countries[$identifier] =
                array_replace($countries[$identifier], $newCountries);
        }

        return $countries;
    }

    /**
     * Retrieve countries not depending on global scope
     *
     * @param string $scope
     * @param int $scopeCode
     * @return array
     */
    private function getAllowedCountries($scope, $scopeCode)
    {
        $reader = $this->getAllowedCountriesReader();
        return $reader->makeCountriesUnique($reader->getCountriesFromConfig($scope, $scopeCode));
    }

    /**
     * Merge allowed countries from stores to websites
     *
     * @param SetupInterface $setup
     * @return void
     */
    private function migrateStoresAllowedCountriesToWebsite(SetupInterface $setup)
    {
        $allowedCountries = [];
        //Process Websites
        foreach ($this->getStoreManager()->getStores() as $store) {
            $allowedCountries = $this->mergeAllowedCountries(
                $allowedCountries,
                $this->getAllowedCountries(ScopeInterface::SCOPE_STORE, $store->getId()),
                $store->getWebsiteId()
            );
        }
        //Process stores
        foreach ($this->getStoreManager()->getWebsites() as $website) {
            $allowedCountries = $this->mergeAllowedCountries(
                $allowedCountries,
                $this->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, $website->getId()),
                $website->getId()
            );
        }

        $connection = $setup->getConnection();

        //Remove everything from stores scope
        $connection->delete(
            $setup->getTable('core_config_data'),
            [
                'path = ?' => AllowedCountries::ALLOWED_COUNTRIES_PATH,
                'scope = ?' => ScopeInterface::SCOPE_STORES
            ]
        );

        //Update websites
        foreach ($allowedCountries as $scopeId => $countries) {
            $connection->update(
                $setup->getTable('core_config_data'),
                [
                    'value' => implode(',', $countries)
                ],
                [
                    'path = ?' => AllowedCountries::ALLOWED_COUNTRIES_PATH,
                    'scope_id = ?' => $scopeId,
                    'scope = ?' => ScopeInterface::SCOPE_WEBSITES
                ]
            );
        }
    }

    /**
     * @param array $entityAttributes
     * @param CustomerSetup $customerSetup
     * @return void
     */
    protected function upgradeAttributes(array $entityAttributes, CustomerSetup $customerSetup)
    {
        foreach ($entityAttributes as $entityType => $attributes) {
            foreach ($attributes as $attributeCode => $attributeData) {
                $attribute = $customerSetup->getEavConfig()->getAttribute($entityType, $attributeCode);
                foreach ($attributeData as $key => $value) {
                    $attribute->setData($key, $value);
                }
                $attribute->save();
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeHash($setup)
    {
        $customerEntityTable = $setup->getTable('customer_entity');

        $select = $setup->getConnection()->select()->from(
            $customerEntityTable,
            ['entity_id', 'password_hash']
        );

        $customers = $setup->getConnection()->fetchAll($select);
        foreach ($customers as $customer) {
            if ($customer['password_hash'] === null) {
                continue;
            }
            list($hash, $salt) = explode(Encryptor::DELIMITER, $customer['password_hash']);

            $newHash = $customer['password_hash'];
            if (strlen($hash) === 32) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_MD5]);
            } elseif (strlen($hash) === 64) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_SHA256]);
            }

            $bind = ['password_hash' => $newHash];
            $where = ['entity_id = ?' => (int)$customer['entity_id']];
            $setup->getConnection()->update($customerEntityTable, $bind, $where);
        }
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function upgradeVersionTwoZeroOne($customerSetup)
    {
        $entityAttributes = [
            'customer' => [
                'website_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'created_in' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'email' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                ],
                'group_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'dob' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'taxvat' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'confirmation' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'created_at' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'gender' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
            ],
            'customer_address' => [
                'company' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'street' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'city' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'country_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'region' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'region_id' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => false,
                ],
                'postcode' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                ],
                'telephone' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                ],
                'fax' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $customerSetup);
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroTwo($customerSetup)
    {
        $entityTypeId = $customerSetup->getEntityTypeId(Customer::ENTITY);
        $attributeId = $customerSetup->getAttributeId($entityTypeId, 'gender');

        $option = ['attribute_id' => $attributeId, 'values' => [3 => 'Not Specified']];
        $customerSetup->addAttributeOption($option);
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroThree($customerSetup)
    {
        $entityAttributes = [
            'customer_address' => [
                'region_id' => [
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => false,
                ],
                'firstname' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
                'lastname' => [
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => true,
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $customerSetup);
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroFour($customerSetup)
    {
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'updated_at',
            [
                'type' => 'static',
                'label' => 'Updated At',
                'input' => 'date',
                'required' => false,
                'sort_order' => 87,
                'visible' => false,
                'system' => false,
            ]
        );
    }

    /**
     * @param CustomerSetup $customerSetup
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeVersionTwoZeroFive($customerSetup, $setup)
    {
        $this->upgradeHash($setup);
        $entityAttributes = [
            'customer_address' => [
                'fax' => [
                    'is_visible' => false,
                    'is_system' => false,
                ],
            ],
        ];
        $this->upgradeAttributes($entityAttributes, $customerSetup);
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroSix($customerSetup)
    {
        $customerSetup->updateEntityType(
            \Magento\Customer\Model\Customer::ENTITY,
            'entity_model',
            \Magento\Customer\Model\ResourceModel\Customer::class
        );
        $customerSetup->updateEntityType(
            \Magento\Customer\Model\Customer::ENTITY,
            'increment_model',
            \Magento\Eav\Model\Entity\Increment\NumericValue::class
        );
        $customerSetup->updateEntityType(
            \Magento\Customer\Model\Customer::ENTITY,
            'entity_attribute_collection',
            \Magento\Customer\Model\ResourceModel\Attribute\Collection::class
        );
        $customerSetup->updateEntityType(
            'customer_address',
            'entity_model',
            \Magento\Customer\Model\ResourceModel\Address::class
        );
        $customerSetup->updateEntityType(
            'customer_address',
            'entity_attribute_collection',
            \Magento\Customer\Model\ResourceModel\Address\Attribute\Collection::class
        );
        $customerSetup->updateAttribute(
            'customer_address',
            'country_id',
            'source_model',
            \Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country::class
        );
        $customerSetup->updateAttribute(
            'customer_address',
            'region',
            'backend_model',
            \Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region::class
        );
        $customerSetup->updateAttribute(
            'customer_address',
            'region_id',
            'source_model',
            \Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Region::class
        );
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroSeven($customerSetup)
    {
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'failures_num',
            [
                'type' => 'static',
                'label' => 'Failures Number',
                'input' => 'hidden',
                'required' => false,
                'sort_order' => 100,
                'visible' => false,
                'system' => true,
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'first_failure',
            [
                'type' => 'static',
                'label' => 'First Failure Date',
                'input' => 'date',
                'required' => false,
                'sort_order' => 110,
                'visible' => false,
                'system' => true,
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'lock_expires',
            [
                'type' => 'static',
                'label' => 'Failures Number',
                'input' => 'date',
                'required' => false,
                'sort_order' => 120,
                'visible' => false,
                'system' => true,
            ]
        );
    }

    /**
     * @param CustomerSetup $customerSetup
     * @return void
     */
    private function upgradeVersionTwoZeroTwelve($customerSetup)
    {
        $customerSetup->updateAttribute('customer_address', 'vat_id', 'frontend_label', 'VAT Number');
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeCustomerPasswordResetlinkExpirationPeriodConfig($setup)
    {
        $configTable = $setup->getTable('core_config_data');

        $setup->getConnection()->update(
            $configTable,
            ['value' => new \Zend_Db_Expr('value*24')],
            ['path = ?' => \Magento\Customer\Model\Customer::XML_PATH_CUSTOMER_RESET_PASSWORD_LINK_EXPIRATION_PERIOD]
        );
    }
}
