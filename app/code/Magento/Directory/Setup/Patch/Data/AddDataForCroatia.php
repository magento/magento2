<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class AddDataForCroatia
 * @package Magento\Directory\Setup\Patch
 */
class AddDataForCroatia implements DataPatchInterface, PatchVersionInterface
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
     * AddDataForCroatia constructor.
     *
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
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var DataInstaller $dataInstaller */
        $dataInstaller = $this->dataInstallerFactory->create();
        $dataInstaller->addCountryRegions(
            $this->moduleDataSetup->getConnection(),
            $this->getDataForCroatia()
        );
    }

    /**
     * Croatian states data.
     *
     * @return array
     */
    private function getDataForCroatia()
    {
        return [
            ['HR', 'HR-01', 'Zagrebačka županija'],
            ['HR', 'HR-02', 'Krapinsko-zagorska županija'],
            ['HR', 'HR-03', 'Sisačko-moslavačka županija'],
            ['HR', 'HR-04', 'Karlovačka županija'],
            ['HR', 'HR-05', 'Varaždinska županija'],
            ['HR', 'HR-06', 'Koprivničko-križevačka županija'],
            ['HR', 'HR-07', 'Bjelovarsko-bilogorska županija'],
            ['HR', 'HR-08', 'Primorsko-goranska županija'],
            ['HR', 'HR-09', 'Ličko-senjska županija'],
            ['HR', 'HR-10', 'Virovitičko-podravska županija'],
            ['HR', 'HR-11', 'Požeško-slavonska županija'],
            ['HR', 'HR-12', 'Brodsko-posavska županija'],
            ['HR', 'HR-13', 'Zadarska županija'],
            ['HR', 'HR-14', 'Osječko-baranjska županija'],
            ['HR', 'HR-15', 'Šibensko-kninska županija'],
            ['HR', 'HR-16', 'Vukovarsko-srijemska županija'],
            ['HR', 'HR-17', 'Splitsko-dalmatinska županija'],
            ['HR', 'HR-18', 'Istarska županija'],
            ['HR', 'HR-19', 'Dubrovačko-neretvanska županija'],
            ['HR', 'HR-20', 'Međimurska županija'],
            ['HR', 'HR-21', 'Grad Zagreb']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            InitializeDirectoryData::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
