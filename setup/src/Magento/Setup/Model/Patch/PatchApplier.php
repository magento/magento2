<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Patch;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Read, create and apply all patches in specific sequence
 */
class PatchApplier
{
    /**
     * @var PatchReader
     */
    private $patchReader;

    /**
     * @var DataPatchFactory
     */
    private $dataPatchFactory;

    /**
     * @var SchemaPatchFactory
     */
    private $schemaPatchFactory;

    /**
     * @var PatchHistory
     */
    private $patchHistory;

    /**
     * @param PatchReader $patchReader
     * @param DataPatchFactory $dataPatchFactory
     * @param SchemaPatchFactory $schemaPatchFactory
     * @param PatchHistory $patchHistory
     */
    public function __construct(
        PatchReader $patchReader,
        DataPatchFactory $dataPatchFactory,
        SchemaPatchFactory $schemaPatchFactory,
        PatchHistory $patchHistory
    )
    {
        $this->patchReader = $patchReader;
        $this->dataPatchFactory = $dataPatchFactory;
        $this->schemaPatchFactory = $schemaPatchFactory;
        $this->patchHistory = $patchHistory;
    }

    /**
     * Apply patches by modules
     *
     * @param ModuleDataSetupInterface | SchemaSetupInterface $setup
     * @param string $moduleName
     */
    public function execute(
        $setup,
        $moduleName = null
    )
    {
        $patches = $this->patchReader->read($moduleName);

        if ($setup instanceof SchemaSetupInterface) {
            $schemaPatchesToApply = $this->patchHistory->getDataPatchesToApply($patches['schema']);
            //Apply schema patches
            foreach ($schemaPatchesToApply as $patchInstanceName) {
                $patch = $this->schemaPatchFactory->create($patchInstanceName);

                if ($this->patchHistory->shouldBeReverted($patch)) {
                    $this->revertSchemaPatch($patch, $setup);
                } else {
                    $this->applySchemaPatches($patch, $setup);
                }
            }
        } elseif ($setup instanceof ModuleDataSetupInterface) {
            $dataPatchesToApply = $this->patchHistory->getDataPatchesToApply($patches['data']);

            //Apply data patches
            foreach ($dataPatchesToApply as $patchInstanceName) {
                $patch = $this->dataPatchFactory->create($patchInstanceName);
                if ($this->patchHistory->shouldBeReverted($patch)) {
                    $this->revertDataPatch($patch, $setup);
                } else {
                    $this->applyDataPatch($patch, $setup);
                }
            }
        }
    }

    /**
     * Revert data patch
     *
     * @param DataPatchInterface $dataPatch
     * @param ModuleDataSetupInterface $dataSetup
     * @throws LocalizedException
     */
    private function revertDataPatch(DataPatchInterface $dataPatch, ModuleDataSetupInterface $dataSetup)
    {
        $connection = $dataSetup->getConnection();

        try {
            $connection->beginTransaction();
            $dataPatch->revert($dataSetup);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException($e->getMessage());
        }
    }

    /**
     * Revert schema patch
     *
     * @param SchemaPatchInterface $schemaPatch
     * @param SchemaSetupInterface $schemaSetup
     * @throws LocalizedException
     */
    private function revertSchemaPatch(SchemaPatchInterface $schemaPatch, SchemaSetupInterface $schemaSetup)
    {
        try {
            $schemaPatch->revert($schemaSetup);
        } catch (\Exception $e) {
            $schemaPatch->apply($schemaSetup);
            throw new LocalizedException($e->getMessage());
        }
    }

    /**
     * Apply data patches
     *
     * @param DataPatchInterface $dataPatch
     * @param ModuleDataSetupInterface $dataSetup
     * @throws LocalizedException
     */
    private function applyDataPatch(DataPatchInterface $dataPatch, ModuleDataSetupInterface $dataSetup)
    {
        if (!$dataPatch->isDisabled()) {
            $connection = $dataSetup->getConnection();

            try {
                $connection->beginTransaction();
                $dataPatch->apply($dataSetup);
                $connection->commit();
            } catch (\Exception $e) {
                $connection->rollBack();
                throw new LocalizedException($e->getMessage());
            }
        }
    }

    /**
     * Apply schema patches
     *
     * @param SchemaPatchInterface $schemaPatch
     * @param SchemaSetupInterface $schemaSetup
     * @throws LocalizedException
     */
    private function applySchemaPatches(SchemaPatchInterface $schemaPatch, SchemaSetupInterface $schemaSetup)
    {
        if (!$schemaPatch->isDisabled()) {
            try {
                $schemaPatch->apply($schemaSetup);
            } catch (\Exception $e) {
                $schemaPatch->revert($schemaSetup);
                throw new LocalizedException($e->getMessage());
            }
        }
    }
}
