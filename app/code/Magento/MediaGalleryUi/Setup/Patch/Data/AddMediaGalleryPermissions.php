<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add child resources permissions for user roles with Magento_Cms::media_gallery permission
 */
class AddMediaGalleryPermissions implements
    DataPatchInterface,
    PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public function apply(): void
    {
        $tableName = $this->moduleDataSetup->getTable('authorization_rule');
        $connection = $this->moduleDataSetup->getConnection();

        if (!$tableName) {
            return;
        }

        $select = $connection->select()
            ->from($tableName, ['role_id'])
            ->where('resource_id = "Magento_Cms::media_gallery"');

        $insertData = $this->getInsertData($connection->fetchCol($select));

        if (!empty($insertData)) {
            $connection->insertMultiple($tableName, $insertData);
        }
    }

    /**
     * Retrieve data to insert to authorization_rule table based on role ids
     *
     * @param array $roleIds
     * @return array
     */
    private function getInsertData(array $roleIds): array
    {
        $newResources = [
            'Magento_MediaGalleryUiApi::insert_assets',
            'Magento_MediaGalleryUiApi::upload_assets',
            'Magento_MediaGalleryUiApi::edit_assets',
            'Magento_MediaGalleryUiApi::delete_assets',
            'Magento_MediaGalleryUiApi::create_folder',
            'Magento_MediaGalleryUiApi::delete_folder'
        ];

        $data = [];

        foreach ($roleIds as $roleId) {
            foreach ($newResources as $resourceId) {
                $data[] = [
                    'role_id' => $roleId,
                    'resource_id' => $resourceId,
                    'permission' => 'allow'
                ];
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion(): string
    {
        return '2.4.2';
    }
}
