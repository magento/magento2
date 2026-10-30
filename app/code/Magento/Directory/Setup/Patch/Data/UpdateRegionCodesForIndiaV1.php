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
 * Update region codes for IN.
 */
class UpdateRegionCodesForIndiaV1 implements DataPatchInterface
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
            'IN',
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
            'CT' => 'CG',
            'DN' => 'DH',
            'OR' => 'OD',
            'TG' => 'TS',
            'UT' => 'UK'
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
            'DN' => 'Dādra and Nagar Haveli and Damān and Diu'
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
