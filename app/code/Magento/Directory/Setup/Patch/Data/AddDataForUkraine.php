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
 * Add Ukraine Regions
 */
class AddDataForUkraine implements DataPatchInterface
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
            $this->getDataForUkraine()
        );

        return $this;
    }

    /**
     * Ukraine regions data.
     *
     * @return array
     */
    private function getDataForUkraine(): array
    {
        return [
            ['UA', 'UA-71', 'Cherkaska oblast'],
            ['UA', 'UA-74', 'Chernihivska oblast'],
            ['UA', 'UA-77', 'Chernivetska oblast'],
            ['UA', 'UA-12', 'Dnipropetrovska oblast'],
            ['UA', 'UA-14', 'Donetska oblast'],
            ['UA', 'UA-26', 'Ivano-Frankivska oblast'],
            ['UA', 'UA-63', 'Kharkivska oblast'],
            ['UA', 'UA-65', 'Khersonska oblast'],
            ['UA', 'UA-68', 'Khmelnytska oblast'],
            ['UA', 'UA-35', 'Kirovohradska oblast'],
            ['UA', 'UA-32', 'Kyivska oblast'],
            ['UA', 'UA-09', 'Luhanska oblast'],
            ['UA', 'UA-46', 'Lvivska oblast'],
            ['UA', 'UA-48', 'Mykolaivska oblast'],
            ['UA', 'UA-51', 'Odeska oblast'],
            ['UA', 'UA-53', 'Poltavska oblast'],
            ['UA', 'UA-56', 'Rivnenska oblast'],
            ['UA', 'UA-59', 'Sumska oblast'],
            ['UA', 'UA-61', 'Ternopilska oblast'],
            ['UA', 'UA-05', 'Vinnytska oblast'],
            ['UA', 'UA-07', 'Volynska oblast'],
            ['UA', 'UA-21', 'Zakarpatska oblast'],
            ['UA', 'UA-23', 'Zaporizka oblast'],
            ['UA', 'UA-18', 'Zhytomyrska oblast'],
            ['UA', 'UA-43', 'Avtonomna Respublika Krym'],
            ['UA', 'UA-30', 'Kyiv'],
            ['UA', 'UA-40', 'Sevastopol'],
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
