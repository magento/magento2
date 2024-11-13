<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Directory\Setup\DataInstallerFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add Ecuador States
 */
class AddDataForEcuador implements DataPatchInterface
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
     * AddDataForEcuador constructor.
     *
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
            $this->getDataForEcuador()
        );

        return $this;
    }

    /**
     * Ecuador states data.
     *
     * @return array
     */
    private function getDataForEcuador()
    {
        return [
            ['EC', 'EC-A', 'Azuay'],
            ['EC', 'EC-B', 'Bolívar'],
            ['EC', 'EC-F', 'Cañar'],
            ['EC', 'EC-C', 'Carchi'],
            ['EC', 'EC-H', 'Chimborazo'],
            ['EC', 'EC-X', 'Cotopaxi'],
            ['EC', 'EC-O', 'El Oro'],
            ['EC', 'EC-E', 'Esmeraldas'],
            ['EC', 'EC-W', 'Galápagos'],
            ['EC', 'EC-G', 'Guayas'],
            ['EC', 'EC-I', 'Imbabura'],
            ['EC', 'EC-L', 'Loja'],
            ['EC', 'EC-R', 'Los Ríos'],
            ['EC', 'EC-M', 'Manabí'],
            ['EC', 'EC-S', 'Morona Santiago'],
            ['EC', 'EC-N', 'Napo'],
            ['EC', 'EC-D', 'Orellana'],
            ['EC', 'EC-Y', 'Pastaza'],
            ['EC', 'EC-P', 'Pichincha'],
            ['EC', 'EC-SE', 'Santa Elena'],
            ['EC', 'EC-SD', 'Santo Domingo de los Tsáchilas'],
            ['EC', 'EC-U', 'Sucumbíos'],
            ['EC', 'EC-T', 'Tungurahua'],
            ['EC', 'EC-Z', 'Zamora Chinchipe'],

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
