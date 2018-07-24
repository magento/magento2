<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
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
            ->where('path = ?', 'catalog/frontend/parse_url_directives');
        $config = $this->moduleDataSetup->getConnection()->fetchAll($select);
        if (!empty($config)) {
            $this->moduleDataSetup->getConnection()->update(
                $configTable,
                ['value' => '1'],
                ['path = ?' => 'catalog/frontend/parse_url_directives', 'value IN (?)' => '0']
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
