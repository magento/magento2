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
 * Add Guyana States
 */
class AddDataForGuyana implements DataPatchInterface
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
     * AddDataForGuyana constructor.
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
            $this->getDataForGuyana()
        );

        return $this;
    }

    /**
     * Guyana states data.
     *
     * @return array
     */
    private function getDataForGuyana()
    {
        return [
            ['GY', 'GY-BA', 'Barima-Waini'],
            ['GY', 'GY-CU', 'Cuyuni-Mazaruni'],
            ['GY', 'GY-DE', 'Demerara-Mahaica'],
            ['GY', 'GY-EB', 'East Berbice-Corentyne'],
            ['GY', 'GY-ES', 'Essequibo Islands-West Demerara'],
            ['GY', 'GY-MA', 'Mahaica-Berbice'],
            ['GY', 'GY-PM', 'Pomeroon-Supenaam'],
            ['GY', 'GY-PT', 'Potaro-Siparuni'],
            ['GY', 'GY-UD', 'Upper Demerara-Berbice'],
            ['GY', 'GY-UT', 'Upper Takutu-Upper Essequibo'],
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
