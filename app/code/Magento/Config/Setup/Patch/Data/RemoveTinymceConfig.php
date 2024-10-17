<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Remove old tinymce versions from the configuration
 */
class RemoveTinymceConfig implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $select = $this->moduleDataSetup->getConnection()->select()
            ->from(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['value']
            )
            ->where('path = ?', 'cms/wysiwyg/editor');

        $configValue = $this->moduleDataSetup->getConnection()->fetchOne($select);

        if ($configValue && (strpos($configValue, 'Tinymce3/tinymce3Adapter') !== false
            || strpos($configValue, 'tiny_mce/tinymce4Adapter') !== false)
        ) {
            $this->moduleDataSetup->getConnection()->query(
                $select->deleteFromSelect(
                    $this->moduleDataSetup->getTable('core_config_data')
                )
            );
        }

        return $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
