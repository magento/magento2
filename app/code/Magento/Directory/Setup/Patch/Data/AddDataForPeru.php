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
 * Add Peru States
 */
class AddDataForPeru implements DataPatchInterface
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
     * AddDataForPeru constructor.
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
            $this->getDataForPeru()
        );

        return $this;
    }

    /**
     * Peru states data.
     *
     * @return array
     */
    private function getDataForPeru()
    {
        return [
            ['PE', 'PE-LMA', 'Municipalidad Metropolitana de Lima'],
            ['PE', 'PE-AMA', 'Amazonas'],
            ['PE', 'PE-ANC', 'Ancash'],
            ['PE', 'PE-APU', 'Apurímac'],
            ['PE', 'PE-ARE', 'Arequipa'],
            ['PE', 'PE-AYA', 'Ayacucho'],
            ['PE', 'PE-CAJ', 'Cajamarca'],
            ['PE', 'PE-CUS', 'Cusco'],
            ['PE', 'PE-CAL', 'El Callao'],
            ['PE', 'PE-HUV', 'Huancavelica'],
            ['PE', 'PE-HUC', 'Huánuco'],
            ['PE', 'PE-ICA', 'Ica'],
            ['PE', 'PE-JUN', 'Junín'],
            ['PE', 'PE-LAL', 'La Libertad'],
            ['PE', 'PE-LAM', 'Lambayeque'],
            ['PE', 'PE-LIM', 'Lima'],
            ['PE', 'PE-LOR', 'Loreto'],
            ['PE', 'PE-MDD', 'Madre de Dios'],
            ['PE', 'PE-MOQ', 'Moquegua'],
            ['PE', 'PE-PAS', 'Pasco'],
            ['PE', 'PE-PIU', 'Piura'],
            ['PE', 'PE-PUN', 'Puno'],
            ['PE', 'PE-SAM', 'San Martín'],
            ['PE', 'PE-TAC', 'Tacna'],
            ['PE', 'PE-TUM', 'Tumbes'],
            ['PE', 'PE-UCA', 'Ucayali'],
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
