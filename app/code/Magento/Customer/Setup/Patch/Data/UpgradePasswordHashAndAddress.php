<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Update passwordHash and address
 */
class UpgradePasswordHashAndAddress implements DataPatchInterface, PatchVersionInterface
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
        $this->upgradeHash();
        $entityAttributes = [
            'customer_address' => [
                'fax' => [
                    'is_visible' => false,
                    'is_system' => false,
                ],
            ],
        ];
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->upgradeAttributes($entityAttributes);

        return $this;
    }

    /**
     * Password hash upgrade
     *
     * @return void
     */
    private function upgradeHash()
    {
        $customerEntityTable = $this->moduleDataSetup->getTable('customer_entity');

        $select = $this->moduleDataSetup->getConnection()->select()->from(
            $customerEntityTable,
            ['entity_id', 'password_hash']
        );

        $customers = $this->moduleDataSetup->getConnection()->fetchAll($select);
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
            $this->moduleDataSetup->getConnection()->update($customerEntityTable, $bind, $where);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            AddCustomerUpdatedAtAttribute::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.5';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
