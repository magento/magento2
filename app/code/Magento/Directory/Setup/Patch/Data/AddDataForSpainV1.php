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

class AddDataForSpainV1 implements DataPatchInterface
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
            $this->getDataForSpain()
        );

        return $this;
    }

    /**
     * Spain regions data.
     *
     * @return array
     */
    private function getDataForSpain(): array
    {
        return [
            ['ES', 'ES-AN', 'Andalucía'],
            ['ES', 'ES-AR', 'Aragón'],
            ['ES', 'ES-BI', 'Bizkaia'],
            ['ES', 'ES-CL', 'Castilla y León'],
            ['ES', 'ES-CM', 'Castilla-La Mancha'],
            ['ES', 'ES-CN', 'Canarias'],
            ['ES', 'ES-CT', 'Catalunya [Cataluña]'],
            ['ES', 'ES-EX', 'Extremadura'],
            ['ES', 'ES-GA', 'Galicia'],
            ['ES', 'ES-M', 'Madrid'],
            ['ES', 'ES-MC', 'Murcia, Región de'],
            ['ES', 'ES-NC', 'Navarra, Comunidad Foral de'],
            ['ES', 'ES-O', 'Asturias'],
            ['ES', 'ES-PV', 'Euskal Herria'],
            ['ES', 'ES-S', 'Cantabria'],
            ['ES', 'ES-V', 'Valencia']
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
