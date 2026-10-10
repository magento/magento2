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

class AddDataForItalyV2 implements DataPatchInterface
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
            $this->getDataForItaly()
        );

        return $this;
    }

    /**
     * Italy regions data.
     *
     * @return array
     */
    private function getDataForItaly(): array
    {
        return [
            ['IT', '21', 'Piemonte'],
            ['IT', '23', 'Valle d\'Aosta'],
            ['IT', '25', 'Lombardia'],
            ['IT', '32', 'Trentino-Alto Adige'],
            ['IT', '34', 'Veneto'],
            ['IT', '36', 'Friuli Venezia Giulia'],
            ['IT', '42', 'Liguria'],
            ['IT', '45', 'Emilia-Romagna'],
            ['IT', '52', 'Toscana'],
            ['IT', '55', 'Umbria'],
            ['IT', '57', 'Marche'],
            ['IT', '62', 'Lazio'],
            ['IT', '65', 'Abruzzo'],
            ['IT', '67', 'Molise'],
            ['IT', '72', 'Campania'],
            ['IT', '75', 'Puglia'],
            ['IT', '77', 'Basilicata'],
            ['IT', '78', 'Calabria'],
            ['IT', '82', 'Sicilia'],
            ['IT', '88', 'Sardegna'],
            ['IT', 'SU', 'Sud Sardegna']
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
