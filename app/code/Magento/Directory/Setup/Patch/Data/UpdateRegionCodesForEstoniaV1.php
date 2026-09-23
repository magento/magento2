<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Directory\Setup\DataInstallerFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update region codes for EE.
 */
class UpdateRegionCodesForEstoniaV1 implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var DataInstallerFactory
     */
    private $dataInstallerFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param DataInstallerFactory $dataInstallerFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        DataInstallerFactory $dataInstallerFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->dataInstallerFactory = $dataInstallerFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(): DataPatchInterface
    {
        /** @var DataInstaller $dataInstaller */
        $dataInstaller = $this->dataInstallerFactory->create();
        $dataInstaller->updateCountryRegionCodes(
            $this->moduleDataSetup->getConnection(),
            'EE',
            $this->getRegionCodeMapping(),
            $this->getRegionNameMapping()
        );

        return $this;
    }

    /**
     * Get region code mapping.
     *
     * @return array
     */
    private function getRegionCodeMapping(): array
    {
        return [
            'EE-44' => 'EE-45',
            'EE-49' => 'EE-50',
            'EE-51' => 'EE-52',
            'EE-57' => 'EE-56',
            'EE-59' => 'EE-60',
            'EE-65' => 'EE-64',
            'EE-67' => 'EE-68',
            'EE-70' => 'EE-71',
            'EE-78' => 'EE-79',
            'EE-82' => 'EE-81',
            'EE-86' => 'EE-87'
        ];
    }

    /**
     * Get region name mapping.
     *
     * @return array
     */
    private function getRegionNameMapping(): array
    {
        return [

        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            InitializeDirectoryData::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
