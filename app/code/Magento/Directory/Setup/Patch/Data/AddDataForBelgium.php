<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add Regions for Belgium.
 */
class AddDataForBelgium implements DataPatchInterface
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
            $this->getDataForBelgium()
        );
    }

    /**
     * Belgium states data.
     *
     * @return array
     */
    private function getDataForBelgium()
    {
        return [
            ['BE', 'VAN', 'Antwerpen'],
            ['BE', 'WBR', 'Brabant wallon'],
            ['BE', 'BRU', 'Brussels-Capital Region'],
            ['BE', 'WHT', 'Hainaut'],
            ['BE', 'VLI', 'Limburg'],
            ['BE', 'WLG', 'Liège'],
            ['BE', 'WLX', 'Luxembourg'],
            ['BE', 'WNA', 'Namur'],
            ['BE', 'VOV', 'Oost-Vlaanderen'],
            ['BE', 'VBR', 'Vlaams-Brabant'],
            ['BE', 'VWV', 'West-Vlaanderen'],
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
