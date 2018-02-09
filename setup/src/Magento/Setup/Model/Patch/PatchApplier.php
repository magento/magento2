<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\ModuleResource;
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
     * @var ModuleResource
     */
    private $moduleResource;

    /**
     * @var PatchHistory
     */
    private $patchHistory;

    /**
     * PatchApplier constructor.
     * @param PatchReader $dataPatchReader
     * @param PatchReader $schemaPatchReader
     * @param PatchRegistryFactory $patchRegistryFactory
     * @param ResourceConnection $resourceConnection
     * @param ModuleResource $moduleResource
     * @param PatchHistory $patchHistory
     */
    public function __construct(
        PatchReader $dataPatchReader,
        PatchReader $schemaPatchReader,
        PatchRegistryFactory $patchRegistryFactory,
        ResourceConnection $resourceConnection,
        ModuleResource $moduleResource,
        PatchHistory $patchHistory
    ) {
        $this->patchRegistryFactory = $patchRegistryFactory;
        $this->dataPatchReader = $dataPatchReader;
        $this->schemaPatchReader = $schemaPatchReader;
        $this->resourceConnection = $resourceConnection;
        $this->moduleResource = $moduleResource;
        $this->patchHistory = $patchHistory;
    }

    /**
     * As we have old scripts and new one we need
     *
     * @param PatchInterface $patch
     * @param string $moduleName
     * @return bool
     */
    private function skipByBackwardIncompatability(PatchInterface $patch, $moduleName)
    {
        $dbVersion = $this->moduleResource->getDataVersion($moduleName);
        return $patch instanceof PatchVersionInterface &&
            version_compare($patch->getVersion(), $dbVersion) <= 0;
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
            if (!$dataPatch instanceof DataPatchInterface) {
                throw new Exception(
                    sprintf("Patch %s should implement DataPatchInterface", get_class($dataPatch))
                );
            }
            /**
             * Due to bacward compatabilities reasons some patches should be skipped
             */
            if ($this->skipByBackwardIncompatability($dataPatch, $moduleName)) {
                continue;
            }

            try {
                $adapter->beginTransaction();
                $dataPatch->apply();
                $this->patchHistory->fixPatch($dataPatch);
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
                $this->patchHistory->fixPatch($schemaPatch);
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
                    $this->patchHistory->revertPatchFromHistory($dataPatch);
                    $adapter->commit();
                } catch (\Exception $e) {
                    $adapter->rollBack();
                    throw new Exception($e->getMessage());
                }
            }
        }
    }
}
