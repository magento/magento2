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
 * Add Paraguay States
 */
class AddDataForParaguay implements DataPatchInterface
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
     * AddDataForParaguay constructor.
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
            $this->getDataForParaguay()
        );

        return $this;
    }

    /**
     * Paraguay states data.
     *
     * @return array
     */
    private function getDataForParaguay()
    {
        return [
            ['PY', 'PY-ASU', 'Asunción'],
            ['PY', 'PY-16', 'Alto Paraguay'],
            ['PY', 'PY-10', 'Alto Paraná'],
            ['PY', 'PY-13', 'Amambay'],
            ['PY', 'PY-19', 'Boquerón'],
            ['PY', 'PY-5', 'Caaguazú'],
            ['PY', 'PY-6', 'Caazapá'],
            ['PY', 'PY-14', 'Canindeyú'],
            ['PY', 'PY-11', 'Central'],
            ['PY', 'PY-1', 'Concepción'],
            ['PY', 'PY-3', 'Cordillera'],
            ['PY', 'PY-4', 'Guairá'],
            ['PY', 'PY-7', 'Itapúa'],
            ['PY', 'PY-8', 'Misiones'],
            ['PY', 'PY-12', 'Ñeembucú'],
            ['PY', 'PY-9', 'Paraguarí'],
            ['PY', 'PY-15', 'Presidente Hayes'],
            ['PY', 'PY-2', 'San Pedro'],
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
