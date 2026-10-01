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
 * Update region codes for FI.
 */
class UpdateRegionCodesForFinlandV1 implements DataPatchInterface
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
    public function apply(): DataPatchInterface
    {
        /** @var DataInstaller $dataInstaller */
        $dataInstaller = $this->dataInstallerFactory->create();
        $dataInstaller->updateCountryRegionCodes(
            $this->moduleDataSetup->getConnection(),
            'FI',
            $this->getRegionCodeMapping(),
            $this->getRegionNameMapping()
        );

        return $this;
    }

    /**
     * Get region code mapping.
     *
     * @return array
     */
    private function getRegionCodeMapping(): array
    {
        return [
            'Ahvenanmaa' => 'FI-01',
            'Etelä-Karjala' => 'FI-02',
            'Etelä-Pohjanmaa' => 'FI-03',
            'Etelä-Savo' => 'FI-04',
            'Kainuu' => 'FI-05',
            'Kanta-Häme' => 'FI-06',
            'Keski-Pohjanmaa' => 'FI-07',
            'Keski-Suomi' => 'FI-08',
            'Kymenlaakso' => 'FI-09',
            'Lappi' => 'FI-10',
            'Päijät-Häme' => 'FI-16',
            'Pirkanmaa' => 'FI-11',
            'Pohjanmaa' => 'FI-12',
            'Pohjois-Karjala' => 'FI-13',
            'Pohjois-Pohjanmaa' => 'FI-14',
            'Pohjois-Savo' => 'FI-15',
            'Satakunta' => 'FI-17',
            'Uusimaa' => 'FI-18',
            'Varsinais-Suomi' => 'FI-19'
        ];
    }

    /**
     * Get region name mapping.
     *
     * @return array
     */
    private function getRegionNameMapping(): array
    {
        return [
            'Ahvenanmaa' => 'Ahvenanmaan maakunta',
            'Etelä-Karjala' => 'Etelä-Karjala',
            'Etelä-Pohjanmaa' => 'Etelä-Pohjanmaa',
            'Etelä-Savo' => 'Etelä-Savo',
            'Kainuu' => 'Kainuu',
            'Kanta-Häme' => 'Kanta-Häme',
            'Keski-Pohjanmaa' => 'Keski-Pohjanmaa',
            'Keski-Suomi' => 'Keski-Suomi',
            'Kymenlaakso' => 'Kymenlaakso',
            'Lappi' => 'Lappi',
            'Päijät-Häme' => 'Päijät-Häme',
            'Pirkanmaa' => 'Pirkanmaa',
            'Pohjanmaa' => 'Pohjanmaa',
            'Pohjois-Karjala' => 'Pohjois-Karjala',
            'Pohjois-Pohjanmaa' => 'Pohjois-Pohjanmaa',
            'Pohjois-Savo' => 'Pohjois-Savo',
            'Satakunta' => 'Satakunta',
            'Uusimaa' => 'Uusimaa',
            'Varsinais-Suomi' => 'Varsinais-Suomi'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            InitializeDirectoryData::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
