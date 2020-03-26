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
 * Add Italy States
 */
class AddDataForItaly implements DataPatchInterface
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
            $this->getDataForItaly()
        );

        return $this;
    }

    /**
     * Italy states data.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function getDataForItaly()
    {
        return [
            ['IT', 'AG', 'Agrigento'],
            ['IT', 'AL', 'Alessandria'],
            ['IT', 'AN', 'Ancona'],
            ['IT', 'AO', 'Aosta'],
            ['IT', 'AQ', 'L\'Aquila'],
            ['IT', 'AR', 'Arezzo'],
            ['IT', 'AP', 'Ascoli-Piceno'],
            ['IT', 'AT', 'Asti'],
            ['IT', 'AV', 'Avellino'],
            ['IT', 'BA', 'Bari'],
            ['IT', 'BT', 'Barletta-Andria-Trani'],
            ['IT', 'BL', 'Belluno'],
            ['IT', 'BN', 'Benevento'],
            ['IT', 'BG', 'Bergamo'],
            ['IT', 'BI', 'Biella'],
            ['IT', 'BO', 'Bologna'],
            ['IT', 'BZ', 'Bolzano'],
            ['IT', 'BS', 'Brescia'],
            ['IT', 'BR', 'Brindisi'],
            ['IT', 'CA', 'Cagliari'],
            ['IT', 'CL', 'Caltanissetta'],
            ['IT', 'CB', 'Campobasso'],
            ['IT', 'CI', 'Carbonia Iglesias'],
            ['IT', 'CE', 'Caserta'],
            ['IT', 'CT', 'Catania'],
            ['IT', 'CZ', 'Catanzaro'],
            ['IT', 'CH', 'Chieti'],
            ['IT', 'CO', 'Como'],
            ['IT', 'CS', 'Cosenza'],
            ['IT', 'CR', 'Cremona'],
            ['IT', 'KR', 'Crotone'],
            ['IT', 'CN', 'Cuneo'],
            ['IT', 'EN', 'Enna'],
            ['IT', 'FM', 'Fermo'],
            ['IT', 'FE', 'Ferrara'],
            ['IT', 'FI', 'Firenze'],
            ['IT', 'FG', 'Foggia'],
            ['IT', 'FC', 'Forli-Cesena'],
            ['IT', 'FR', 'Frosinone'],
            ['IT', 'GE', 'Genova'],
            ['IT', 'GO', 'Gorizia'],
            ['IT', 'GR', 'Grosseto'],
            ['IT', 'IM', 'Imperia'],
            ['IT', 'IS', 'Isernia'],
            ['IT', 'SP', 'La-Spezia'],
            ['IT', 'LT', 'Latina'],
            ['IT', 'LE', 'Lecce'],
            ['IT', 'LC', 'Lecco'],
            ['IT', 'LI', 'Livorno'],
            ['IT', 'LO', 'Lodi'],
            ['IT', 'LU', 'Lucca'],
            ['IT', 'MC', 'Macerata'],
            ['IT', 'MN', 'Mantova'],
            ['IT', 'MS', 'Massa-Carrara'],
            ['IT', 'MT', 'Matera'],
            ['IT', 'VS', 'Medio Campidano'],
            ['IT', 'ME', 'Messina'],
            ['IT', 'MI', 'Milano'],
            ['IT', 'MO', 'Modena'],
            ['IT', 'MB', 'Monza-Brianza'],
            ['IT', 'NA', 'Napoli'],
            ['IT', 'NO', 'Novara'],
            ['IT', 'NU', 'Nuoro'],
            ['IT', 'OG', 'Ogliastra'],
            ['IT', 'OT', 'Olbia Tempio'],
            ['IT', 'OR', 'Oristano'],
            ['IT', 'PD', 'Padova'],
            ['IT', 'PA', 'Palermo'],
            ['IT', 'PR', 'Parma'],
            ['IT', 'PV', 'Pavia'],
            ['IT', 'PG', 'Perugia'],
            ['IT', 'PU', 'Pesaro-Urbino'],
            ['IT', 'PE', 'Pescara'],
            ['IT', 'PC', 'Piacenza'],
            ['IT', 'PI', 'Pisa'],
            ['IT', 'PT', 'Pistoia'],
            ['IT', 'PN', 'Pordenone'],
            ['IT', 'PZ', 'Potenza'],
            ['IT', 'PO', 'Prato'],
            ['IT', 'RG', 'Ragusa'],
            ['IT', 'RA', 'Ravenna'],
            ['IT', 'RC', 'Reggio-Calabria'],
            ['IT', 'RE', 'Reggio-Emilia'],
            ['IT', 'RI', 'Rieti'],
            ['IT', 'RN', 'Rimini'],
            ['IT', 'RM', 'Roma'],
            ['IT', 'RO', 'Rovigo'],
            ['IT', 'SA', 'Salerno'],
            ['IT', 'SS', 'Sassari'],
            ['IT', 'SV', 'Savona'],
            ['IT', 'SI', 'Siena'],
            ['IT', 'SR', 'Siracusa'],
            ['IT', 'SO', 'Sondrio'],
            ['IT', 'TA', 'Taranto'],
            ['IT', 'TE', 'Teramo'],
            ['IT', 'TR', 'Terni'],
            ['IT', 'TO', 'Torino'],
            ['IT', 'TP', 'Trapani'],
            ['IT', 'TN', 'Trento'],
            ['IT', 'TV', 'Treviso'],
            ['IT', 'TS', 'Trieste'],
            ['IT', 'UD', 'Udine'],
            ['IT', 'VA', 'Varese'],
            ['IT', 'VE', 'Venezia'],
            ['IT', 'VB', 'Verbania'],
            ['IT', 'VC', 'Vercelli'],
            ['IT', 'VR', 'Verona'],
            ['IT', 'VV', 'Vibo-Valentia'],
            ['IT', 'VI', 'Vicenza'],
            ['IT', 'VT', 'Viterbo'],
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
