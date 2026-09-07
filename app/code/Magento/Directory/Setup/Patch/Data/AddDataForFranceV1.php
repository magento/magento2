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

class AddDataForFranceV1 implements DataPatchInterface
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
            $this->getDataForFrance()
        );

        return $this;
    }

    /**
     * France regions data.
     *
     * @return array
     */
    private function getDataForFrance(): array
    {
        return [
            ['FR', '20R', 'Corse'],
            ['FR', '69M', 'Métropole de Lyon'],
            ['FR', '6AE', 'Alsace'],
            ['FR', '971', 'Guadeloupe'],
            ['FR', '972', 'Martinique'],
            ['FR', '973', 'Guyane (française)'],
            ['FR', '974', 'La Réunion'],
            ['FR', '976', 'Mayotte'],
            ['FR', 'ARA', 'Auvergne-Rhône-Alpes'],
            ['FR', 'BFC', 'Bourgogne-Franche-Comté'],
            ['FR', 'BL', 'Saint-Barthélemy'],
            ['FR', 'BRE', 'Bretagne'],
            ['FR', 'CP', 'Clipperton'],
            ['FR', 'CVL', 'Centre-Val de Loire'],
            ['FR', 'GES', 'Grand-Est'],
            ['FR', 'HDF', 'Hauts-de-France'],
            ['FR', 'IDF', 'Île-de-France'],
            ['FR', 'MF', 'Saint-Martin'],
            ['FR', 'NAQ', 'Nouvelle-Aquitaine'],
            ['FR', 'NC', 'Nouvelle-Calédonie'],
            ['FR', 'NOR', 'Normandie'],
            ['FR', 'OCC', 'Occitanie'],
            ['FR', 'PAC', 'Provence-Alpes-Côte-d’Azur'],
            ['FR', 'PDL', 'Pays-de-la-Loire'],
            ['FR', 'PF', 'Polynésie française'],
            ['FR', 'PM', 'Saint-Pierre-et-Miquelon'],
            ['FR', 'TF', 'Terres australes françaises'],
            ['FR', 'WF', 'Wallis-et-Futuna']
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
