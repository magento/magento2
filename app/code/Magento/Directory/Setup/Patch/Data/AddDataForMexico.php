<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Adds Mexican States
 */
class AddDataForMexico implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Directory\Setup\DataInstallerFactory
     */
    private $dataInstallerFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Directory\Setup\DataInstallerFactory $dataInstallerFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Directory\Setup\DataInstallerFactory $dataInstallerFactory
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
            $this->getDataForMexico()
        );
    }

    /**
     * Mexican states data.
     *
     * @return array
     */
    private function getDataForMexico()
    {
        return [
            ['MX', 'AGU', 'Aguascalientes'],
            ['MX', 'BCN', 'Baja California'],
            ['MX', 'BCS', 'Baja California Sur'],
            ['MX', 'CAM', 'Campeche'],
            ['MX', 'CHP', 'Chiapas'],
            ['MX', 'CHH', 'Chihuahua'],
            ['MX', 'CMX', 'Ciudad de México'],
            ['MX', 'COA', 'Coahuila'],
            ['MX', 'COL', 'Colima'],
            ['MX', 'DUR', 'Durango'],
            ['MX', 'MEX', 'Estado de México'],
            ['MX', 'GUA', 'Guanajuato'],
            ['MX', 'GRO', 'Guerrero'],
            ['MX', 'HID', 'Hidalgo'],
            ['MX', 'JAL', 'Jalisco'],
            ['MX', 'MIC', 'Michoacán'],
            ['MX', 'MOR', 'Morelos'],
            ['MX', 'NAY', 'Nayarit'],
            ['MX', 'NLE', 'Nuevo León'],
            ['MX', 'OAX', 'Oaxaca'],
            ['MX', 'PUE', 'Puebla'],
            ['MX', 'QUE', 'Querétaro'],
            ['MX', 'ROO', 'Quintana Roo'],
            ['MX', 'SLP', 'San Luis Potosí'],
            ['MX', 'SIN', 'Sinaloa'],
            ['MX', 'SON', 'Sonora'],
            ['MX', 'TAB', 'Tabasco'],
            ['MX', 'TAM', 'Tamaulipas'],
            ['MX', 'TLA', 'Tlaxcala'],
            ['MX', 'VER', 'Veracruz'],
            ['MX', 'YUC', 'Yucatán'],
            ['MX', 'ZAC', 'Zacatecas']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            InitializeDirectoryData::class,
            AddDataForAustralia::class,
            AddDataForCroatia::class,
            AddDataForIndia::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.4';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
