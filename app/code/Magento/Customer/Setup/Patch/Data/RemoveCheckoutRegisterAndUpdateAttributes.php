<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Address;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country;
use Magento\Customer\Model\ResourceModel\Attribute\Collection;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Increment\NumericValue;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Remove register and update attributes for checkout
 */
class RemoveCheckoutRegisterAndUpdateAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('customer_form_attribute'),
            ['form_code = ?' => 'checkout_register']
        );
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->updateEntityType(
            Customer::ENTITY,
            'entity_model',
            \Magento\Customer\Model\ResourceModel\Customer::class
        );
        $customerSetup->updateEntityType(
            Customer::ENTITY,
            'increment_model',
            NumericValue::class
        );
        $customerSetup->updateEntityType(
            Customer::ENTITY,
            'entity_attribute_collection',
            Collection::class
        );
        $customerSetup->updateEntityType(
            'customer_address',
            'entity_model',
            Address::class
        );
        $customerSetup->updateEntityType(
            'customer_address',
            'entity_attribute_collection',
            Address\Attribute\Collection::class
        );
        $customerSetup->updateAttribute(
            'customer_address',
            'country_id',
            'source_model',
            Country::class
        );
        $customerSetup->updateAttribute(
            'customer_address',
            'region',
            'backend_model',
            Region::class
        );
        $customerSetup->updateAttribute(
            'customer_address',
            'region_id',
            'source_model',
            Address\Attribute\Source\Region::class
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            UpgradePasswordHashAndAddress::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.6';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
