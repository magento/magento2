<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);
namespace Magento\Framework\Setup\Patch;

use Magento\Framework\Module\ModuleList;
use Magento\Framework\Setup\UpToDateValidatorInterface;
use Magento\Framework\Setup\DetailProviderInterface;

/**
 * Allows to validate if data patches is up to date or not
 */
class UpToDateSchema implements UpToDateValidatorInterface, DetailProviderInterface
{
    /**
     * @var PatchHistory
     */
    private $patchHistory;

    /**
     * @var PatchReader
     */
    private $patchReader;

    /**
     * @var PatchBackwardCompatability
     */
    private $patchBackwardCompatability;

    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * UpToDateData constructor.
     * @param PatchHistory $patchHistory
     * @param PatchReader $schemaReader
     * @param PatchBackwardCompatability $patchBackwardCompatability
     * @param ModuleList $moduleList
     */
    public function __construct(
        PatchHistory $patchHistory,
        PatchReader $schemaReader,
        PatchBackwardCompatability $patchBackwardCompatability,
        ModuleList $moduleList
    ) {
        $this->patchHistory = $patchHistory;
        $this->patchReader = $schemaReader;
        $this->patchBackwardCompatability = $patchBackwardCompatability;
        $this->moduleList = $moduleList;
    }

    /**
     * Get not update schema information
     *
     * @return string
     */
    public function getNotUpToDateMessage() : string
    {
        return 'Schema patches are not up to date';
    }

    /**
     * Check module list schema update
     *
     * @return bool
     */
    public function isUpToDate() : bool
    {
        foreach ($this->moduleList->getNames() as $moduleName) {
            foreach ($this->patchReader->read($moduleName) as $patchName) {
                if (!$this->patchBackwardCompatability->isSkipableBySchemaSetupVersion($patchName, $moduleName) &&
                    !$this->patchHistory->isApplied($patchName)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get detailed information about unapplied schema patches
     *
     * @return array
     */
    public function getDetails(): array
    {
        $unappliedPatches = [];

        foreach ($this->moduleList->getNames() as $moduleName) {
            foreach ($this->patchReader->read($moduleName) as $patchName) {
                if (!$this->patchBackwardCompatability->isSkipableBySchemaSetupVersion($patchName, $moduleName) &&
                    !$this->patchHistory->isApplied($patchName)) {
                    $unappliedPatches[] = [
                        'patch' => $patchName,
                        'module' => $moduleName
                    ];
                }
            }
        }

        if (empty($unappliedPatches)) {
            return [];
        }

        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'unapplied_patches' => $unappliedPatches
        ];
    }
}
