<?php

namespace Test\TestModule1\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $tableName = $setup->getTable('test_table1');
        $setup->getConnection()->insertArray(
            $tableName,
            ['column12', 'column13'],
            [['Test data 1', 'Test data 2'], ['Test data 3', 'Test data 4'], ['Test data 5', 'Test data 6']]
        );
    }
}
