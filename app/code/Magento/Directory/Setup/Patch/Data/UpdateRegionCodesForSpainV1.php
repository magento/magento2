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
 * Update region codes for ES.
 */
class UpdateRegionCodesForSpainV1 implements DataPatchInterface
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
            'ES',
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
            'A Coruсa' => 'ES-C',
            'Alava' => 'ES-VI',
            'Albacete' => 'ES-AB',
            'Alicante' => 'ES-A',
            'Almeria' => 'ES-AL',
            'Asturias' => 'ES-AS',
            'Avila' => 'ES-AV',
            'Badajoz' => 'ES-BA',
            'Baleares' => 'ES-IB',
            'Barcelona' => 'ES-B',
            'Burgos' => 'ES-BU',
            'Caceres' => 'ES-CC',
            'Cadiz' => 'ES-CA',
            'Cantabria' => 'ES-CB',
            'Castellon' => 'ES-CS',
            'Ceuta' => 'ES-CE',
            'Ciudad Real' => 'ES-CR',
            'Cordoba' => 'ES-CO',
            'Cuenca' => 'ES-CU',
            'Girona' => 'ES-GI',
            'Granada' => 'ES-GR',
            'Guadalajara' => 'ES-GU',
            'Guipuzcoa' => 'ES-SS',
            'Huelva' => 'ES-H',
            'Huesca' => 'ES-HU',
            'Jaen' => 'ES-J',
            'La Rioja' => 'ES-LO',
            'Las Palmas' => 'ES-GC',
            'Leon' => 'ES-LE',
            'Lleida' => 'ES-L',
            'Lugo' => 'ES-LU',
            'Madrid' => 'ES-MD',
            'Malaga' => 'ES-MA',
            'Melilla' => 'ES-ML',
            'Murcia' => 'ES-MU',
            'Navarra' => 'ES-NA',
            'Ourense' => 'ES-OR',
            'Palencia' => 'ES-P',
            'Pontevedra' => 'ES-PO',
            'Salamanca' => 'ES-SA',
            'Santa Cruz de Tenerife' => 'ES-TF',
            'Segovia' => 'ES-SG',
            'Sevilla' => 'ES-SE',
            'Soria' => 'ES-SO',
            'Tarragona' => 'ES-T',
            'Teruel' => 'ES-TE',
            'Toledo' => 'ES-TO',
            'Valencia' => 'ES-VC',
            'Valladolid' => 'ES-VA',
            'Zamora' => 'ES-ZA',
            'Zaragoza' => 'ES-Z'
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
            'A Coruсa' => 'A Coruña [La Coruña]',
            'Alava' => 'Araba',
            'Albacete' => 'Albacete',
            'Alicante' => 'Alacant',
            'Almeria' => 'Almería',
            'Asturias' => 'Asturias, Principado de',
            'Avila' => 'Ávila',
            'Badajoz' => 'Badajoz',
            'Baleares' => 'Illes Balears [Islas Baleares]',
            'Barcelona' => 'Barcelona',
            'Burgos' => 'Burgos',
            'Caceres' => 'Cáceres',
            'Cadiz' => 'Cádiz',
            'Cantabria' => 'Cantabria',
            'Castellon' => 'Castellón',
            'Ceuta' => 'Ceuta',
            'Ciudad Real' => 'Ciudad Real',
            'Cordoba' => 'Córdoba',
            'Cuenca' => 'Cuenca',
            'Girona' => 'Girona [Gerona]',
            'Granada' => 'Granada',
            'Guadalajara' => 'Guadalajara',
            'Guipuzcoa' => 'Gipuzkoa',
            'Huelva' => 'Huelva',
            'Huesca' => 'Huesca',
            'Jaen' => 'Jaén',
            'La Rioja' => 'La Rioja',
            'Las Palmas' => 'Las Palmas',
            'Leon' => 'León',
            'Lleida' => 'Lleida [Lérida]',
            'Lugo' => 'Lugo',
            'Madrid' => 'Madrid, Comunidad de',
            'Malaga' => 'Málaga',
            'Melilla' => 'Melilla',
            'Murcia' => 'Murcia',
            'Navarra' => 'Navarra',
            'Ourense' => 'Ourense [Orense]',
            'Palencia' => 'Palencia',
            'Pontevedra' => 'Pontevedra',
            'Salamanca' => 'Salamanca',
            'Santa Cruz de Tenerife' => 'Santa Cruz de Tenerife',
            'Segovia' => 'Segovia',
            'Sevilla' => 'Sevilla',
            'Soria' => 'Soria',
            'Tarragona' => 'Tarragona',
            'Teruel' => 'Teruel',
            'Toledo' => 'Toledo',
            'Valencia' => 'Valenciana, Comunidad',
            'Valladolid' => 'Valladolid',
            'Zamora' => 'Zamora',
            'Zaragoza' => 'Zaragoza'
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
