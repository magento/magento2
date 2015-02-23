<?php

namespace Test\TestModule1\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.0.1') < 0) {
            $tableName = $setup->getTable('test_table1');
            $connection = $setup->getConnection();
            $condition = $connection->prepareSqlCondition(
                'column12',
                [['like' => '%1%'], ['like' => '%3%'],]
            );
            $connection->delete($tableName, $condition);
        }
    }
}
