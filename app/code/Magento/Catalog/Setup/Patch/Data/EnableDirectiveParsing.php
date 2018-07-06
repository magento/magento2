<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class EnableDirectiveParsing
 * @package Magento\Catalog\Setup\Patch
 */
class EnableDirectiveParsing implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $configTable = $this->moduleDataSetup->getTable('core_config_data');
        $select = $this->moduleDataSetup->getConnection()->select()
            ->from($configTable)
            ->where('path = ?', \Magento\Catalog\Helper\Data::CONFIG_PARSE_URL_DIRECTIVES);
        $config = $this->moduleDataSetup->getConnection()->fetchAll($select);
        if (!empty($config)) {
            $this->moduleDataSetup->getConnection()->update(
                $configTable,
                ['value' => new \Zend_Db_Expr('1')],
                ['path = ?' => \Magento\Catalog\Helper\Data::CONFIG_PARSE_URL_DIRECTIVES, 'value IN (?)' => '0']
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
