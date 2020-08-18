<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Update config to Tinymce4 if Tinymce3 adapter is used.
 */
class UnsetTinymce3 implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * CreateDefaultPages constructor.
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
        try {
            $connection = $this->moduleDataSetup->getConnection();
            $table = $this->moduleDataSetup->getTable('core_config_data');
            $select = $connection
                ->select()
                ->from(
                    $table,
                    ['value']
                )
                ->where('path = ?', 'cms/wysiwyg/editor');

            if (strpos($connection->fetchOne($select), 'Tinymce3/tinymce3Adapter') !== false) {
                $row = [
                    'value' => 'mage/adminhtml/wysiwyg/tiny_mce/tinymce4Adapter'
                ];
                $where = $connection->quoteInto(
                    'path = ?',
                    'cms/wysiwyg/editor'
                );
                $connection->update(
                    $table,
                    $row,
                    $where
                );
            }
            return $this;
        } catch (\Exception $e) {
            return $this;
        }
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
    public static function getVersion()
    {
        return '2.3.6';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
