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
 * Add Bolivia States
 */
class AddDataForBolivia implements DataPatchInterface
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
     * AddDataForBolivia constructor.
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
            $this->getDataForBolivia()
        );

        return $this;
    }

    /**
     * Bolivia states data.
     *
     * @return array
     */
    private function getDataForBolivia()
    {
        return [
            ['BO', 'BO-C', 'Cochabamba'],
            ['BO', 'BO-H', 'Chuquisaca'],
            ['BO', 'BO-B', 'El Beni'],
            ['BO', 'BO-L', 'La Paz'],
            ['BO', 'BO-O', 'Oruro'],
            ['BO', 'BO-N', 'Pando'],
            ['BO', 'BO-P', 'Potosí'],
            ['BO', 'BO-S', 'Santa Cruz'],
            ['BO', 'BO-T', 'Tarija'],
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
