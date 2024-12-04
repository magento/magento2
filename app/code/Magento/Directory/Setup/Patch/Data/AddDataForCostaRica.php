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
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Add Costa Rica States/Regions
 */
class AddDataForCostaRica implements DataPatchInterface, PatchVersionInterface
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
            $this->getDataForCostaRica()
        );

        return $this;
    }

    /**
     * Costa Rica states data.Pura Vida :)
     *
     * @return array
     */
    private function getDataForCostaRica(): array
    {
        return [
            ['CR', 'CR-SJ', 'San José'],
            ['CR', 'CR-AL', 'Alajuela'],
            ['CR', 'CR-CA', 'Cartago'],
            ['CR', 'CR-HE', 'Heredia'],
            ['CR', 'CR-GU', 'Guanacaste'],
            ['CR', 'CR-PU', 'Puntarenas'],
            ['CR', 'CR-LI', 'Limón']
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

    /**
     * Get version
     *
     * @return string
     */
    public static function getVersion()
    {
        return '2.4.2';
    }
}
