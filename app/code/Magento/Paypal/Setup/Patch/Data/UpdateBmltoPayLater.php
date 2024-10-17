<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update bml config settings to the equivalent paylater settings
 */
class UpdateBmltoPayLater implements DataPatchInterface
{
    /**
     * BML config path
     */
    private const BMLPATH = 'payment/paypal_express_bml/';

    /**
     * PayLater config path
     */
    private const PAYLATERPATH = 'payment/paypal_paylater/';

    /**
     * @var array
     */
    private $bmlToPayLater = [
        [
            'pages' => ['productpage', 'checkout'],
            'data' => [
                'position' => [
                    'name' =>'position',
                    'values' => [['options' =>['0' => 'header', '1' => 'near_pp_button']]],
                    'requires' => [
                        'header' => ['name' => 'stylelayout', 'value' => 'flex'],
                        'near_pp_button' => ['name' => 'stylelayout', 'value' => 'text']
                    ]
                ],
                'size' => [
                    'name' => 'ratio',
                    'values' => [
                        [
                            'options' => [
                                '190x100' => '8x1',
                                '234x60' => '8x1',
                                '300x50' => '8x1',
                                '468x60' => '8x1',
                                '728x90' => '20x1',
                                '800x66' => '20x1'
                            ],
                            'depends' => ['name' => 'position', 'value' => '0']
                        ]
                    ]
                ]
            ]
        ],
        [
            'pages' => ['homepage', 'categorypage'],
            'data' => [
                'position' => [
                    'name' =>'position',
                    'values' => [['options' => ['0' => 'header', '1' => 'sidebar']]],
                    'requires' => [
                        'header' => ['name' => 'stylelayout', 'value' => 'flex'],
                        'sidebar' => ['name' => 'stylelayout', 'value' => 'flex']
                    ]
                ],
                'size' => [
                    'name' => 'ratio',
                    'values' => [
                        [
                            'options' => [
                                '190x100' => '8x1',
                                '234x60' => '8x1',
                                '300x50' => '8x1',
                                '468x60' => '8x1',
                                '728x90' => '20x1',
                                '800x66' => '20x1'
                            ],
                            'depends' => ['name' => 'position', 'value' => '0']
                        ],
                        [
                            'options' => [
                                '120x90' => '1x1',
                                '190x100' => '1x1',
                                '234x60' => '1x1',
                                '120x240' => '1x1',
                                '120x600' => '1x4',
                                '234x400' => '1x1',
                                '250x250' => '1x1'
                            ],
                            'depends' => ['name' => 'position', 'value' => '1']
                        ]
                    ]
                ]
            ]
        ]
    ];

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PrepareInitialConfig constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $select = $this->moduleDataSetup->getConnection()->select()
            ->from(
                $this->moduleDataSetup->getTable('core_config_data'),
                ['path', 'value']
            )
            ->where('path LIKE ?', self::BMLPATH . '%');
        $bmlSettings = $this->moduleDataSetup->getConnection()->fetchPairs($select);

        foreach ($bmlSettings as $bmlPath => $bmlValue) {
            $setting = str_replace(self::BMLPATH, '', $bmlPath);
            $settingParts = explode('_', $setting);
            if (count($settingParts) !== 2) {
                continue;
            }
            $page = $settingParts[0];
            $setting = $settingParts[1];
            $payLaterPage = $page === 'checkout' ? 'cartpage' : $page;
            $payLaterPath = self::PAYLATERPATH . $payLaterPage;

            if (array_key_exists(self::BMLPATH . $page . '_display', $bmlSettings)
                && $bmlSettings[self::BMLPATH . $page . '_display'] === '0'
            ) {
                continue;
            }

            foreach ($this->bmlToPayLater as $bmlToPayLaterSetting) {
                if (in_array($page, $bmlToPayLaterSetting['pages'])
                    && array_key_exists($setting, $bmlToPayLaterSetting['data'])
                ) {
                    $pageSetting = $bmlToPayLaterSetting['data'][$setting];

                    $this->convertAndSaveConfigValues($bmlSettings, $pageSetting, $payLaterPath, $page, $bmlValue);
                }
            }
        }

        return $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Convert BML settings to PayLater and save
     *
     * @param array $bmlSettings
     * @param array $pageSetting
     * @param string $payLaterPath
     * @param string $page
     * @param string $bmlValue
     */
    private function convertAndSaveConfigValues(
        array $bmlSettings,
        array $pageSetting,
        string $payLaterPath,
        string $page,
        string $bmlValue
    ) {
        foreach ($pageSetting['values'] as $pageSettingValues) {
            $dependsPath = isset($pageSettingValues['depends'])
                ? self::BMLPATH . $page . '_' . $pageSettingValues['depends']['name']
                : '';

            if (!array_key_exists('depends', $pageSettingValues)
                || (array_key_exists($dependsPath, $bmlSettings)
                    && $bmlSettings[$dependsPath] === $pageSettingValues['depends']['value'])
            ) {
                $path = $payLaterPath . '_' . $pageSetting['name'];
                $value = $pageSettingValues['options'][$bmlValue];
                $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    [
                        'scope' => 'default',
                        'scope_id' => 0,
                        'path' => $path,
                        'value' => $value
                    ]
                );
                if (array_key_exists('requires', $pageSetting)
                    && array_key_exists($value, $pageSetting['requires'])
                ) {
                    $requiredPath = $payLaterPath . '_' . $pageSetting['requires'][$value]['name'];
                    $requiredValue = $pageSetting['requires'][$value]['value'];
                    $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                        $this->moduleDataSetup->getTable('core_config_data'),
                        [
                            'scope' => 'default',
                            'scope_id' => 0,
                            'path' => $requiredPath,
                            'value' => $requiredValue
                        ]
                    );
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
