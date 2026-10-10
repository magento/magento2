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
 * Add Saudi Arabia Regions
 */
class AddDataForSaudiArabia implements DataPatchInterface
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
    public function apply()
    {
        /** @var DataInstaller $dataInstaller */
        $dataInstaller = $this->dataInstallerFactory->create();
        $dataInstaller->addCountryRegions(
            $this->moduleDataSetup->getConnection(),
            $this->getDataForSaudiArabia()
        );

        return $this;
    }

    /**
     * Saudi Arabia regions data.
     *
     * @return array
     */
    private function getDataForSaudiArabia()
    {
        return [
            ['SA', 'SA-01', 'Riyadh'],
            ['SA', 'SA-02', 'Makkah'],
            ['SA', 'SA-03', 'Madinah'],
            ['SA', 'SA-04', 'Eastern Province'],
            ['SA', 'SA-05', 'Qassim'],
            ['SA', 'SA-06', 'Hail'],
            ['SA', 'SA-07', 'Tabuk'],
            ['SA', 'SA-08', 'Northern Borders'],
            ['SA', 'SA-09', 'Jazan'],
            ['SA', 'SA-10', 'Najran'],
            ['SA', 'SA-11', 'Baha'],
            ['SA', 'SA-12', 'Jouf'],
            ['SA', 'SA-14', 'Asir'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            InitializeDirectoryData::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
