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
 * Add Suriname States
 */
class AddDataForSuriname implements DataPatchInterface
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
     * AddDataForSuriname constructor.
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
            $this->getDataForSuriname()
        );

        return $this;
    }

    /**
     * Suriname states data.
     *
     * @return array
     */
    private function getDataForSuriname()
    {
        return [
            ['SR', 'SR-BR', 'Brokopondo'],
            ['SR', 'SR-CM', 'Commewijne'],
            ['SR', 'SR-CR', 'Coronie'],
            ['SR', 'SR-MA', 'Marowijne'],
            ['SR', 'SR-NI', 'Nickerie'],
            ['SR', 'SR-PR', 'Para'],
            ['SR', 'SR-PM', 'Paramaribo'],
            ['SR', 'SR-SA', 'Saramacca'],
            ['SR', 'SR-SI', 'Sipaliwini'],
            ['SR', 'SR-WA', 'Wanica'],
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
