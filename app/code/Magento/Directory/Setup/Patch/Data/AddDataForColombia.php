<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddDataForColombia
 */
class AddDataForColombia implements DataPatchInterface
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
            $this->getDataForColombia()
        );
    }

    /**
     * Colombia states data.
     *
     * @return array
     */
    private function getDataForColombia()
    {
        return [
            ['CO', 'CO-AMA', 'Amazonas'],
            ['CO', 'CO-ANT', 'Antioquia'],
            ['CO', 'CO-ARA', 'Arauca'],
            ['CO', 'CO-ATL', 'Atlántico'],
            ['CO', 'CO-BOL', 'Bolívar'],
            ['CO', 'CO-BOY', 'Boyacá'],
            ['CO', 'CO-CAL', 'Caldas'],
            ['CO', 'CO-CAQ', 'Caquetá'],
            ['CO', 'CO-CAS', 'Casanare'],
            ['CO', 'CO-CAU', 'Cauca'],
            ['CO', 'CO-CES', 'Cesar'],
            ['CO', 'CO-CHO', 'Chocó'],
            ['CO', 'CO-COR', 'Córdoba'],
            ['CO', 'CO-CUN', 'Cundinamarca'],
            ['CO', 'CO-GUA', 'Guainía'],
            ['CO', 'CO-GUV', 'Guaviare'],
            ['CO', 'CO-HUL', 'Huila'],
            ['CO', 'CO-LAG', 'La Guajira'],
            ['CO', 'CO-MAG', 'Magdalena'],
            ['CO', 'CO-MET', 'Meta'],
            ['CO', 'CO-NAR', 'Nariño'],
            ['CO', 'CO-NSA', 'Norte de Santander'],
            ['CO', 'CO-PUT', 'Putumayo'],
            ['CO', 'CO-QUI', 'Quindío'],
            ['CO', 'CO-RIS', 'Risaralda'],
            ['CO', 'CO-SAP', 'San Andrés y Providencia'],
            ['CO', 'CO-SAN', 'Santander'],
            ['CO', 'CO-SUC', 'Sucre'],
            ['CO', 'CO-TOL', 'Tolima'],
            ['CO', 'CO-VAC', 'Valle del Cauca'],
            ['CO', 'CO-VAU', 'Vaupés'],
            ['CO', 'CO-VID', 'Vichada'],
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
