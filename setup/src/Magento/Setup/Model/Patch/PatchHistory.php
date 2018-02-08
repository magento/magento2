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
    private $patchesRegistry = null;

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
     * All patches are store in patch_list table
     * @see self::TABLE_NAME
     *
     * @return array
     */
    private function getAppliedPatches()
    {
        if ($this->patchesRegistry === null) {
            $adapter = $this->resourceConnection->getConnection();
            $filterSelect = $adapter->select()
                ->from($this->resourceConnection->getTableName(self::TABLE_NAME), self::CLASS_NAME);
            $this->patchesRegistry = $adapter->fetchCol($filterSelect);
        }

        return $this->patchesRegistry;
    }

    /**
     * Check whether patch was applied on the system or not
     *
     * @param string $patchName
     * @return bool
     */
    public function isApplied($patchName)
    {
        return isset($this->getAppliedPatches()[$patchName]);
    }
}
