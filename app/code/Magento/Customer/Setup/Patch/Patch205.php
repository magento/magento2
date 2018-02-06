<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch205 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param CustomerSetupFactory $customerSetupFactory @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory,
                                \Magento\Eav\Model\Config $eavConfig)
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $this->upgradeVersionTwoZeroFive($customerSetup, $setup);


        $this->eavConfig->clear();
        $setup->endSetup();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


    private function upgradeVersionTwoZeroFive($customerSetup, $setup
    )
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

    private function upgradeHash($setup
    )
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

    private function upgradeAttributes(array $entityAttributes, CustomerSetup $customerSetup
    )
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
}
