<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Fedex\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateFedexInternationalPriority implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * UpdateFedexInternationalPriority constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     *
     * Apply the patch to update INTERNATIONAL_PRIORITY to FEDEX_INTERNATIONAL_PRIORITY
     */
    public function apply()
    {
        $conn = $this->moduleDataSetup->getConnection();
        $configDataTable = $this->moduleDataSetup->getTable('core_config_data');
        $paths = [
            'carriers/fedex/allowed_methods',
            'carriers/fedex/free_method'
        ];
        foreach ($paths as $path) {
            $select = $conn->select()
                ->from($configDataTable)
                ->where('path = ?', $path);
            $rows = $conn->fetchAll($select);
            foreach ($rows as $row) {
                $values = explode(',', $row['value']);
                $updated = false;
                foreach ($values as &$value) {
                    if (trim($value) === 'INTERNATIONAL_PRIORITY') {
                        $value = 'FEDEX_INTERNATIONAL_PRIORITY';
                        $updated = true;
                    }
                }
                unset($value);
                if ($updated) {
                    $newValue = implode(',', $values);
                    $conn->update(
                        $configDataTable,
                        ['value' => $newValue],
                        ['config_id = ?' => $row['config_id']]
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            ConfigureFedexDefaults::class
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
