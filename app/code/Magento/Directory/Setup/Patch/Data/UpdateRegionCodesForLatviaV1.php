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
 * Update region codes for LV.
 */
class UpdateRegionCodesForLatviaV1 implements DataPatchInterface
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
            'LV',
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
            'Ādažu novads' => 'LV-011',
            'Ķekavas novads' => 'LV-052',
            'Līvānu novads' => 'LV-056',
            'LV-AI' => 'LV-002',
            'LV-AL' => 'LV-007',
            'LV-BL' => 'LV-015',
            'LV-BU' => 'LV-016',
            'LV-CE' => 'LV-022',
            'LV-DO' => 'LV-026',
            'LV-GU' => 'LV-033',
            'LV-JK' => 'LV-042',
            'LV-JL' => 'LV-041',
            'LV-KR' => 'LV-047',
            'LV-KU' => 'LV-050',
            'LV-LM' => 'LV-054',
            'LV-LU' => 'LV-058',
            'LV-MA' => 'LV-059',
            'LV-OG' => 'LV-067',
            'LV-PR' => 'LV-073',
            'LV-RE' => 'LV-077',
            'LV-SA' => 'LV-088',
            'LV-TA' => 'LV-097',
            'LV-TU' => 'LV-099',
            'LV-VE' => 'LV-106',
            'LV-VK' => 'LV-101',
            'LV-VM' => 'LV-113',
            'Mārupes novads' => 'LV-062',
            'Olaines novads' => 'LV-068',
            'Ropažu novads' => 'LV-080',
            'Salaspils novads' => 'LV-087',
            'Saulkrastu novads' => 'LV-089',
            'Siguldas novads' => 'LV-091',
            'Smiltenes novads' => 'LV-094',
            'Varakļānu novads' => 'LV-102'
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
