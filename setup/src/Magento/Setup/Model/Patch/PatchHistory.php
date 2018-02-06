<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

use Magento\Framework\App\ResourceConnection;

/**
 * This is registry of all patches, that are already applied on database
 */
class PatchHistory
{
    /**
     * Table name where patche names will be persisted
     */
    const TABLE_NAME = 'patch_list';

    /**
     * Name of a patch
     */
    const CLASS_NAME = "name";

    /**
     * Patch type for schema patches
     */
    const SCHEMA_PATCH_TYPE = 'schema';

    /**
     * Patch type for data patches
     */
    const DATA_PATCH_TYPE = 'data';

    /**
     * @var array
     */
    private $patchesRegistry = [];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * PatchHistory constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Read and cache data patches from db
     *
     * @return array
     */
    private function getAppliedDataPatches()
    {
        if (!isset($this->patchesRegistry[self::DATA_PATCH_TYPE])) {
            $adapter = $this->resourceConnection->getConnection();
            $filterSelect = $adapter->select()
                ->from($this->resourceConnection->getTableName(self::TABLE_NAME), self::CLASS_NAME);
            $filterSelect->where('patch_type = ?', self::DATA_PATCH_TYPE);
            $this->patchesRegistry[self::DATA_PATCH_TYPE] = $adapter->fetchCol($filterSelect);
        }

        return $this->patchesRegistry[self::DATA_PATCH_TYPE];
    }

    /**
     * Retrieve all data patches, that were applied
     *
     * @param array $readPatches
     * @return array
     */
    public function getDataPatchesToApply(array $readPatches)
    {
        $appliedPatches = $this->getAppliedDataPatches();
        return array_filter($readPatches, function (array $patch) use ($appliedPatches) {
            return !in_array($patch[self::CLASS_NAME], $appliedPatches);
        });
    }

    /**
     * Retrieve all data patches, that were applied
     *
     * @param array $readPatches
     * @return array
     */
    public function getSchemaPatchesToApply(array $readPatches)
    {
        $appliedPatches = $this->getAppliedSchemaPatches();
        return array_filter($readPatches, function (array $patch) use ($appliedPatches) {
            return !in_array($patch[self::CLASS_NAME], $appliedPatches);
        });
    }

    /**
     * Retrieve all schema patches, that were applied
     *
     * @return array
     */
    private function getAppliedSchemaPatches()
    {
        if (!isset($this->patchesRegistry[self::SCHEMA_PATCH_TYPE])) {
            $adapter = $this->resourceConnection->getConnection();
            $filterSelect = $adapter->select()
                ->from($this->resourceConnection->getTableName(self::TABLE_NAME), self::CLASS_NAME);
            $filterSelect->where('patch_type = ?', self::SCHEMA_PATCH_TYPE);
            $this->patchesRegistry[self::SCHEMA_PATCH_TYPE] = $adapter->fetchCol($filterSelect);
        }

        return $this->patchesRegistry[self::SCHEMA_PATCH_TYPE];
    }

    /**
     * Check whether patch should be reverted
     *
     * @param PatchDisableInterface $patch
     * @return bool
     */
    public function shouldBeReverted(PatchDisableInterface $patch)
    {
        if ($patch->isDisabled()) {
            return in_array(get_class($patch), $this->getAppliedDataPatches()) ||
                in_array(get_class($patch), $this->getAppliedSchemaPatches());
        }

        return false;
    }
}
