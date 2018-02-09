<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
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
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class RemoveCheckoutRegisterAndUpdateAttributes
 * @package Magento\Customer\Setup\Patch
 */
class RemoveCheckoutRegisterAndUpdateAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * RemoveCheckoutRegisterAndUpdateAttributes constructor.
     * @param ResourceConnection $resourceConnection
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getConnection()->getTableName('customer_form_attribute'),
            ['form_code = ?' => 'checkout_register']
        );
        $customerSetup = $this->customerSetupFactory->create(['resourceConnection' => $this->resourceConnection]);
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
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpgradePasswordHashAndAddress::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.6';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
