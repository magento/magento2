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
 * Add China States
 */
class AddDataForChina implements DataPatchInterface
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
            $this->getDataForChina()
        );
    }

    /**
     * China states data.
     *
     * @return array
     */
    private function getDataForChina()
    {
        return [
            ['CN', 'CN-AH', 'Anhui Sheng'],
            ['CN', 'CN-BJ', 'Beijing Shi'],
            ['CN', 'CN-CQ', 'Chongqing Shi'],
            ['CN', 'CN-FJ', 'Fujian Sheng'],
            ['CN', 'CN-GS', 'Gansu Sheng'],
            ['CN', 'CN-GD', 'Guangdong Sheng'],
            ['CN', 'CN-GX', 'Guangxi Zhuangzu Zizhiqu'],
            ['CN', 'CN-GZ', 'Guizhou Sheng'],
            ['CN', 'CN-HI', 'Hainan Sheng'],
            ['CN', 'CN-HE', 'Hebei Sheng'],
            ['CN', 'CN-HL', 'Heilongjiang Sheng'],
            ['CN', 'CN-HA', 'Henan Sheng'],
            ['CN', 'CN-HK', 'Hong Kong SAR'],
            ['CN', 'CN-HB', 'Hubei Sheng'],
            ['CN', 'CN-HN', 'Hunan Sheng'],
            ['CN', 'CN-JS', 'Jiangsu Sheng'],
            ['CN', 'CN-JX', 'Jiangxi Sheng'],
            ['CN', 'CN-JL', 'Jilin Sheng'],
            ['CN', 'CN-LN', 'Liaoning Sheng'],
            ['CN', 'CN-MO', 'Macao SAR'],
            ['CN', 'CN-NM', 'Nei Mongol Zizhiqu'],
            ['CN', 'CN-NX', 'Ningxia Huizi Zizhiqu'],
            ['CN', 'CN-QH', 'Qinghai Sheng'],
            ['CN', 'CN-SN', 'Shaanxi Sheng'],
            ['CN', 'CN-SD', 'Shandong Sheng'],
            ['CN', 'CN-SH', 'Shanghai Shi'],
            ['CN', 'CN-SX', 'Shanxi Sheng'],
            ['CN', 'CN-SC', 'Sichuan Sheng'],
            ['CN', 'CN-TW', 'Taiwan Sheng'],
            ['CN', 'CN-TJ', 'Tianjin Shi'],
            ['CN', 'CN-XJ', 'Xinjiang Uygur Zizhiqu'],
            ['CN', 'CN-XZ', 'Xizang Zizhiqu'],
            ['CN', 'CN-YN', 'Yunnan Sheng'],
            ['CN', 'CN-ZJ', 'Zhejiang Sheng'],
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
