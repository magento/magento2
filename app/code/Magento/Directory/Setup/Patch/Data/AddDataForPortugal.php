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
 * Add Portugal States
 */
class AddDataForPortugal implements DataPatchInterface
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
     * AddDataForPortugal constructor.
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
            $this->getDataForPortugal()
        );

        return $this;
    }

    /**
     * Portugal states data.
     *
     * @return array
     */
    private function getDataForPortugal()
    {
        return [
            ['PT', 'PT-01', 'Aveiro'],
            ['PT', 'PT-02', 'Beja'],
            ['PT', 'PT-03', 'Braga'],
            ['PT', 'PT-04', 'Bragança'],
            ['PT', 'PT-05', 'Castelo Branco'],
            ['PT', 'PT-06', 'Coimbra'],
            ['PT', 'PT-07', 'Évora'],
            ['PT', 'PT-08', 'Faro'],
            ['PT', 'PT-09', 'Guarda'],
            ['PT', 'PT-10', 'Leiria'],
            ['PT', 'PT-11', 'Lisboa'],
            ['PT', 'PT-12', 'Portalegre'],
            ['PT', 'PT-13', 'Porto'],
            ['PT', 'PT-14', 'Santarém'],
            ['PT', 'PT-15', 'Setúbal'],
            ['PT', 'PT-16', 'Viana do Castelo'],
            ['PT', 'PT-17', 'Vila Real'],
            ['PT', 'PT-18', 'Viseu'],
            ['PT', 'PT-20', 'Região Autónoma dos Açores'],
            ['PT', 'PT-30', 'Região Autónoma da Madeira']
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
