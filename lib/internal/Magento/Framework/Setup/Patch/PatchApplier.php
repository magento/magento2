<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Patch;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Setup\Exception as SetupException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Apply patches per specific module
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PatchApplier
{
    /**
     * Flag means, that we need to read schema patches
     */
    public const SCHEMA_PATCH = 'schema';

    /**
     * Flag means, that we need to read data patches
     */
    public const DATA_PATCH = 'data';

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
     * @var PatchHistory
     */
    private $patchHistory;

    /**
     * @var PatchFactory
     */
    private $patchFactory;

    /**
     * @var \Magento\Framework\Setup\SetupInterface
     */
    private $schemaSetup;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PatchBackwardCompatability
     */
    private $patchBackwardCompatability;

    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * PatchApplier constructor.
     * @param PatchReader $dataPatchReader
     * @param PatchReader $schemaPatchReader
     * @param PatchRegistryFactory $patchRegistryFactory
     * @param ResourceConnection $resourceConnection
     * @param PatchBackwardCompatability $patchBackwardCompatability
     * @param PatchHistory $patchHistory
     * @param PatchFactory $patchFactory
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Setup\SchemaSetupInterface $schemaSetup
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ModuleList $moduleList
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(Magento.TypeDuplication)
     */
    public function __construct(
        PatchReader $dataPatchReader,
        PatchReader $schemaPatchReader,
        PatchRegistryFactory $patchRegistryFactory,
        ResourceConnection $resourceConnection,
        PatchBackwardCompatability $patchBackwardCompatability,
        PatchHistory $patchHistory,
        PatchFactory $patchFactory,
        ObjectManagerInterface $objectManager,
        \Magento\Framework\Setup\SchemaSetupInterface $schemaSetup,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        ModuleList $moduleList
    ) {
        $this->patchRegistryFactory = $patchRegistryFactory;
        $this->dataPatchReader = $dataPatchReader;
        $this->schemaPatchReader = $schemaPatchReader;
        $this->resourceConnection = $resourceConnection;
        $this->patchHistory = $patchHistory;
        $this->patchFactory = $patchFactory;
        $this->schemaSetup = $schemaSetup;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->objectManager = $objectManager;
        $this->patchBackwardCompatability = $patchBackwardCompatability;
        $this->moduleList = $moduleList;
    }

    /**
     * Apply all patches for one module
     *
     * @param null|string $moduleName
     * @throws SetupException
     */
    public function applyDataPatch($moduleName = null)
    {
        $registry = $this->prepareRegistry($moduleName, self::DATA_PATCH);
        foreach ($registry as $dataPatch) {
            /**
             * Due to backward compatabilities reasons some patches should be skipped
             */
            if ($this->patchBackwardCompatability->isSkipableByDataSetupVersion($dataPatch, $moduleName)) {
                $this->patchHistory->fixPatch($dataPatch);
                continue;
            }

            $dataPatch = $this->objectManager->create(
                '\\' . $dataPatch,
                ['moduleDataSetup' => $this->moduleDataSetup]
            );
            if (!$dataPatch instanceof DataPatchInterface) {
                throw new SetupException(
                    new Phrase("Patch %1 should implement DataPatchInterface", [get_class($dataPatch)])
                );
            }
            if ($dataPatch instanceof NonTransactionableInterface) {
                $dataPatch->apply();
                $this->patchHistory->fixPatch(get_class($dataPatch));
            } else {
                try {
                    $this->moduleDataSetup->getConnection()->beginTransaction();
                    $dataPatch->apply();
                    $this->patchHistory->fixPatch(get_class($dataPatch));
                    foreach ($dataPatch->getAliases() as $patchAlias) {
                        if (!$this->patchHistory->isApplied($patchAlias)) {
                            $this->patchHistory->fixPatch($patchAlias);
                        }
                    }
                    $this->moduleDataSetup->getConnection()->commit();
                } catch (\Exception $e) {
                    $this->moduleDataSetup->getConnection()->rollBack();
                    throw new SetupException(
                        new Phrase(
                            'Unable to apply data patch %1 for module %2. Original exception message: %3',
                            [
                                get_class($dataPatch),
                                $moduleName,
                                $e->getMessage()
                            ]
                        ),
                        $e
                    );
                } finally {
                    unset($dataPatch);
                }
            }
        }
    }

    /**
     * Register all patches in registry in order to manipulate chains and dependencies of patches of patches
     *
     * @param string $moduleName
     * @param string $patchType
     * @return PatchRegistry
     */
    private function prepareRegistry($moduleName, $patchType)
    {
        $reader = $patchType === self::DATA_PATCH ? $this->dataPatchReader : $this->schemaPatchReader;
        $registry = $this->patchRegistryFactory->create();

        //Prepare modules to read
        if ($moduleName === null) {
            $patchNames = [];
            foreach ($this->moduleList->getNames() as $moduleName) {
                $patchNames += $reader->read($moduleName);
            }
        } else {
            $patchNames = $reader->read($moduleName);
        }

        foreach ($patchNames as $patchName) {
            $registry->registerPatch($patchName);
        }

        return $registry;
    }

    /**
     * Apply all patches for one module
     *
     * Please note: that schema patches are not revertable
     *
     * @param null|string $moduleName
     * @throws SetupException
     */
    public function applySchemaPatch($moduleName = null)
    {
        $registry = $this->prepareRegistry($moduleName, self::SCHEMA_PATCH);
        foreach ($registry as $schemaPatch) {
            try {
                /**
                 * Skip patches that were applied in old style
                 */
                if ($this->patchBackwardCompatability->isSkipableBySchemaSetupVersion($schemaPatch, $moduleName)) {
                    $this->patchHistory->fixPatch($schemaPatch);
                    continue;
                }
                /**
                 * @var SchemaPatchInterface $schemaPatch
                 */
                $schemaPatch = $this->patchFactory->create($schemaPatch, ['schemaSetup' => $this->schemaSetup]);
                $schemaPatch->apply();
                $this->patchHistory->fixPatch(get_class($schemaPatch));
                foreach ($schemaPatch->getAliases() as $patchAlias) {
                    if (!$this->patchHistory->isApplied($patchAlias)) {
                        $this->patchHistory->fixPatch($patchAlias);
                    }
                }
            } catch (\Exception $e) {
                throw new SetupException(
                    new Phrase(
                        'Unable to apply patch %1 for module %2. Original exception message: %3',
                        [
                            get_class($schemaPatch),
                            $moduleName,
                            $e->getMessage()
                        ]
                    )
                );
            } finally {
                unset($schemaPatch);
            }
        }
    }

    /**
     * Revert data patches for specific module
     *
     * @param null|string $moduleName
     * @throws SetupException
     */
    public function revertDataPatches($moduleName = null)
    {
        $registry = $this->prepareRegistry($moduleName, self::DATA_PATCH);
        $adapter = $this->moduleDataSetup->getConnection();

        foreach ($registry->getReverseIterator() as $dataPatch) {
            $dataPatch = $this->objectManager->create(
                '\\' . $dataPatch,
                ['moduleDataSetup' => $this->moduleDataSetup]
            );
            if ($dataPatch instanceof PatchRevertableInterface) {
                try {
                    $adapter->beginTransaction();
                    /** @var PatchRevertableInterface|DataPatchInterface $dataPatch */
                    $dataPatch->revert();
                    $this->patchHistory->revertPatchFromHistory(get_class($dataPatch));
                    $adapter->commit();
                } catch (\Exception $e) {
                    $adapter->rollBack();
                    throw new SetupException(new Phrase($e->getMessage()));
                } finally {
                    unset($dataPatch);
                }
            }
        }
    }
}
