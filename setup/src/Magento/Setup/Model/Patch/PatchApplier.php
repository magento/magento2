<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Exception;

/**
 * Apply patches per specific module
 */
class PatchApplier
{
    /**
     * @var PatchRegistryFactory
     */
    private $patchRegistryFactory;

    /**
     * @var PatchReader
     */
    private $dataPatchReader;

    /**
     * @var PatchReader
     */
    private $schemaPatchReader;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * PatchApplier constructor.
     * @param PatchReader $dataPatchReader
     * @param PatchReader $schemaPatchReader
     * @param PatchRegistryFactory $patchRegistryFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        PatchReader $dataPatchReader,
        PatchReader $schemaPatchReader,
        PatchRegistryFactory $patchRegistryFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->patchRegistryFactory = $patchRegistryFactory;
        $this->dataPatchReader = $dataPatchReader;
        $this->schemaPatchReader = $schemaPatchReader;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Apply all patches for one module
     *
     * @param null | string $moduleName
     * @throws Exception
     */
    public function applyDataPatch($moduleName = null)
    {
        $dataPatches = $this->dataPatchReader->read($moduleName);
        $registry = $this->prepareRegistry($dataPatches);
        $adapter = $this->resourceConnection->getConnection();

        /**
         * @var DataPatchInterface $dataPatch
         */
        foreach ($registry as $dataPatch) {
            try {
                $adapter->beginTransaction();
                $dataPatch->apply();
                $adapter->commit();
            } catch (\Exception $e) {
                $adapter->rollBack();
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Register all patches in registry in order to manipulate chains and dependencies of patches
     * of patches
     *
     * @param array $patchNames
     * @return PatchRegistry
     */
    private function prepareRegistry(array $patchNames)
    {
        $registry = $this->patchRegistryFactory->create();

        foreach ($patchNames as $patchName) {
            $registry->registerPatch($patchName);
        }

        return $registry;
    }

    /**
     * Apply all patches for one module
     *
     * @param null | string $moduleName
     * @throws Exception
     */
    public function applySchemaPatch($moduleName = null)
    {
        $schemaPatches = $this->schemaPatchReader->read($moduleName);
        $registry = $this->prepareRegistry($schemaPatches);

        /**
         * @var SchemaPatchInterface $schemaPatch
         */
        foreach ($registry as $schemaPatch) {
            try {
                $schemaPatch->apply();
            } catch (\Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Revert data patches for specific module
     *
     * @param null | string $moduleName
     * @throws Exception
     */
    public function revertDataPatches($moduleName = null)
    {
        $dataPatches = $this->dataPatchReader->read($moduleName);
        $registry = $this->prepareRegistry($dataPatches);
        $adapter = $this->resourceConnection->getConnection();

        /**
         * @var DataPatchInterface $dataPatch
         */
        foreach ($registry->getReverseIterator() as $dataPatch) {
            if ($dataPatch instanceof PatchRevertableInterface) {
                try {
                    $adapter->beginTransaction();
                    $dataPatch->revert();
                    $adapter->commit();
                } catch (\Exception $e) {
                    $adapter->rollBack();
                    throw new Exception($e->getMessage());
                }
            }
        }
    }
}
