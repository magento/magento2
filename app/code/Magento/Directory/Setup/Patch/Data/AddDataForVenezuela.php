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
 * Add Venezuela States
 */
class AddDataForVenezuela implements DataPatchInterface
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
     * AddDataForVenezuela constructor.
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
            $this->getDataForVenezuela()
        );

        return $this;
    }

    /**
     * Venezuela states data.
     *
     * @return array
     */
    private function getDataForVenezuela()
    {
        return [
            ['VE', 'VE-W', 'Dependencias Federales'],
            ['VE', 'VE-A', 'Distrito Capital'],
            ['VE', 'VE-Z', 'Amazonas'],
            ['VE', 'VE-B', 'Anzoátegui'],
            ['VE', 'VE-C', 'Apure'],
            ['VE', 'VE-D', 'Aragua'],
            ['VE', 'VE-E', 'Barinas'],
            ['VE', 'VE-F', 'Bolívar'],
            ['VE', 'VE-G', 'Carabobo'],
            ['VE', 'VE-H', 'Cojedes'],
            ['VE', 'VE-Y', 'Delta Amacuro'],
            ['VE', 'VE-I', 'Falcón'],
            ['VE', 'VE-J', 'Guárico'],
            ['VE', 'VE-K', 'Lara'],
            ['VE', 'VE-L', 'Mérida'],
            ['VE', 'VE-M', 'Miranda'],
            ['VE', 'VE-N', 'Monagas'],
            ['VE', 'VE-O', 'Nueva Esparta'],
            ['VE', 'VE-P', 'Portuguesa'],
            ['VE', 'VE-R', 'Sucre'],
            ['VE', 'VE-S', 'Táchira'],
            ['VE', 'VE-T', 'Trujillo'],
            ['VE', 'VE-X', 'Vargas'],
            ['VE', 'VE-U', 'Yaracuy'],
            ['VE', 'VE-V', 'Zulia'],
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
