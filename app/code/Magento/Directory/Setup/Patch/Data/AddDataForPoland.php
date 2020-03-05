<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See PLPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Directory\Setup\DataInstallerFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add Poland States
 */
class AddDataForPoland implements DataPatchInterface
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
            $this->getDataForPoland()
        );
    }

    /**
     * Poland states data.
     *
     * @return array
     */
    private function getDataForPoland()
    {
        return [
            ['PL', 'PL-02', 'dolnośląskie'],
            ['PL', 'PL-04', 'kujawsko-pomorskie'],
            ['PL', 'PL-06', 'lubelskie'],
            ['PL', 'PL-08', 'lubuskie'],
            ['PL', 'PL-10', 'łódzkie'],
            ['PL', 'PL-12', 'małopolskie'],
            ['PL', 'PL-14', 'mazowieckie'],
            ['PL', 'PL-16', 'opolskie'],
            ['PL', 'PL-18', 'podkarpackie'],
            ['PL', 'PL-20', 'podlaskie'],
            ['PL', 'PL-22', 'pomorskie'],
            ['PL', 'PL-24', 'śląskie'],
            ['PL', 'PL-26', 'świętokrzyskie'],
            ['PL', 'PL-28', 'warmińsko-mazurskie'],
            ['PL', 'PL-30', 'wielkopolskie'],
            ['PL', 'PL-32', 'zachodniopomorskie'],
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
