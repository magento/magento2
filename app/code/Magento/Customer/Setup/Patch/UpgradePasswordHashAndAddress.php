<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\VersionedDataPatch;

/**
 * Class UpgradePasswordHashAndAddress
 * @package Magento\Customer\Setup\Patch
 */
class UpgradePasswordHashAndAddress implements DataPatchInterface, VersionedDataPatch
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
     * UpgradePasswordHashAndAddress constructor.
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
        $this->upgradeHash();
        $entityAttributes = [
            'customer_address' => [
                'fax' => [
                    'is_visible' => false,
                    'is_system' => false,
                ],
            ],
        ];
        $customerSetup = $this->customerSetupFactory->create(['resourceConnection' => $this->resourceConnection]);
        $customerSetup->upgradeAttributes($entityAttributes);

    }

    /**
     * @return void
     */
    private function upgradeHash()
    {
        $customerEntityTable = $this->resourceConnection->getConnection()->getTableName('customer_entity');

        $select = $this->resourceConnection->getConnection()->select()->from(
            $customerEntityTable,
            ['entity_id', 'password_hash']
        );

        $customers = $this->resourceConnection->getConnection()->fetchAll($select);
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
            $this->resourceConnection->getConnection()->update($customerEntityTable, $bind, $where);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddCustomerUpdatedAtAttribute::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '2.0.5';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
