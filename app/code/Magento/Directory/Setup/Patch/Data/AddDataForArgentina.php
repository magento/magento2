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
 * Add Argentina States
 */
class AddDataForArgentina implements DataPatchInterface
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
     * AddDataForArgentina constructor.
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
            $this->getDataForArgentina()
        );

        return $this;
    }

    /**
     * Argentina states data.
     *
     * @return array
     */
    private function getDataForArgentina()
    {
        return [
            ['AR', 'AR-C', 'Ciudad Autónoma de Buenos Aires'],
            ['AR', 'AR-B', 'Buenos Aires'],
            ['AR', 'AR-K', 'Catamarca'],
            ['AR', 'AR-H', 'Chaco'],
            ['AR', 'AR-U', 'Chubut'],
            ['AR', 'AR-X', 'Córdoba'],
            ['AR', 'AR-W', 'Corrientes'],
            ['AR', 'AR-E', 'Entre Ríos'],
            ['AR', 'AR-P', 'Formosa'],
            ['AR', 'AR-Y', 'Jujuy'],
            ['AR', 'AR-L', 'La Pampa'],
            ['AR', 'AR-F', 'La Rioja'],
            ['AR', 'AR-M', 'Mendoza'],
            ['AR', 'AR-N', 'Misiones'],
            ['AR', 'AR-Q', 'Neuquén'],
            ['AR', 'AR-R', 'Río Negro'],
            ['AR', 'AR-A', 'Salta'],
            ['AR', 'AR-J', 'San Juan'],
            ['AR', 'AR-D', 'San Luis'],
            ['AR', 'AR-Z', 'Santa Cruz'],
            ['AR', 'AR-S', 'Santa Fe'],
            ['AR', 'AR-G', 'Santiago del Estero'],
            ['AR', 'AR-V', 'Tierra del Fuego'],
            ['AR', 'AR-T', 'Tucumán'],
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
