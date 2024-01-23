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
 * Add Albania States
 */
class AddDataForAlbania implements DataPatchInterface
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
     * AddDataForAlbania constructor.
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
            $this->getDataForAlbania()
        );

        return $this;
    }

    /**
     * Albania states data.
     *
     * @return array
     */
    private function getDataForAlbania()
    {
        return [
            ['AL', 'AL-01', 'Berat'],
            ['AL', 'AL-09', 'Dibër'],
            ['AL', 'AL-02', 'Durrës'],
            ['AL', 'AL-03', 'Elbasan'],
            ['AL', 'AL-04', 'Fier'],
            ['AL', 'AL-05', 'Gjirokastër'],
            ['AL', 'AL-06', 'Korçë'],
            ['AL', 'AL-07', 'Kukës'],
            ['AL', 'AL-08', 'Lezhë'],
            ['AL', 'AL-10', 'Shkodër'],
            ['AL', 'AL-11', 'Tiranë'],
            ['AL', 'AL-12', 'Vlorë']
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
