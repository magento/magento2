<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Directory\Setup\DataInstallerFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Add Indonesia Provinces
 */
class AddDataForIndonesia implements DataPatchInterface
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
            $this->getDataForIndonesia()
        );

        return $this;
    }

    /**
     * Indonesia provinces data.
     *
     * @return array
     */
    private function getDataForIndonesia(): array
    {
        return [
            ['ID', 'ID-AC', 'Aceh'],
            ['ID', 'ID-BA', 'Bali'],
            ['ID', 'ID-BT', 'Banten'],
            ['ID', 'ID-BE', 'Bengkulu'],
            ['ID', 'ID-YO', 'Daerah Istimewa Yogyakarta'],
            ['ID', 'ID-JK', 'DKI Jakarta'],
            ['ID', 'ID-GO', 'Gorontalo'],
            ['ID', 'ID-JA', 'Jambi'],
            ['ID', 'ID-JB', 'Jawa Barat'],
            ['ID', 'ID-JT', 'Jawa Tengah'],
            ['ID', 'ID-JI', 'Jawa Timur'],
            ['ID', 'ID-KB', 'Kalimantan Barat'],
            ['ID', 'ID-KS', 'Kalimantan Selatan'],
            ['ID', 'ID-KT', 'Kalimantan Tengah'],
            ['ID', 'ID-KI', 'Kalimantan Timur'],
            ['ID', 'ID-KU', 'Kalimantan Utara'],
            ['ID', 'ID-BB', 'Kepulauan Bangka Belitung'],
            ['ID', 'ID-KR', 'Kepulauan Riau'],
            ['ID', 'ID-LA', 'Lampung'],
            ['ID', 'ID-MA', 'Maluku'],
            ['ID', 'ID-MU', 'Maluku Utara'],
            ['ID', 'ID-NB', 'Nusa Tenggara Barat'],
            ['ID', 'ID-NT', 'Nusa Tenggara Timur'],
            ['ID', 'ID-PA', 'Papua'],
            ['ID', 'ID-PB', 'Papua Barat'],
            ['ID', 'ID-PD', 'Papua Barat Daya'],
            ['ID', 'ID-PE', 'Papua Pegunungan'],
            ['ID', 'ID-PS', 'Papua Selatan'],
            ['ID', 'ID-PT', 'Papua Tengah'],
            ['ID', 'ID-RI', 'Riau'],
            ['ID', 'ID-SR', 'Sulawesi Barat'],
            ['ID', 'ID-SN', 'Sulawesi Selatan'],
            ['ID', 'ID-ST', 'Sulawesi Tengah'],
            ['ID', 'ID-SG', 'Sulawesi Tenggara'],
            ['ID', 'ID-SA', 'Sulawesi Utara'],
            ['ID', 'ID-SB', 'Sumatera Barat'],
            ['ID', 'ID-SS', 'Sumatera Selatan'],
            ['ID', 'ID-SU', 'Sumatera Utara'],
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
