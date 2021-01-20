<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config\Plugin;

use Magento\Config\App\Config\Source\DumpConfigSourceAggregated;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Theme\Model\Design\Config\Plugin\Dump;

class DumpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Dump
     */
    private $dumpPlugin;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * Themes id to full path mapping
     *
     * @var array
     */
    private $themes = [
        1 => 'adminhtml/Magento/backend',
        2 => 'frontend/Magento/blank',
        3 => 'frontend/Magento/luma',
    ];

    /**
     * @var ListInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $themeList;

    protected function setUp(): void
    {
        $this->arrayManager = new ArrayManager();
        $this->themeList = $this->getMockBuilder(ListInterface::class)
            ->setMethods(['getItemById', 'getThemeByFullPath'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->prepareThemeMock();

        $this->dumpPlugin = new Dump($this->themeList, $this->arrayManager);
    }

    /**
     * @param array $actualResult
     * @param array $expectedResult
     * @dataProvider getDumpConfigDataProvider
     */
    public function testAfterGet($actualResult, $expectedResult)
    {
        $dumpConfig = $this->getMockBuilder(DumpConfigSourceAggregated::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals($expectedResult, $this->dumpPlugin->afterGet($dumpConfig, $actualResult));
    }

    /**
     * Prepare Theme mock according to theme map
     *
     * @return void
     */
    private function prepareThemeMock()
    {
        $themesMap = [];
        foreach ($this->themes as $themeId => $themeFullPath) {
            $themeMock = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)
                ->getMockForAbstractClass();
            $themeMock->expects(static::any())->method('getFullPath')->willReturn($themeFullPath);

            $themesMap[] = [$themeId, $themeMock];
        }

        $this->themeList->expects(static::any())->method('getItemById')->willReturnMap($themesMap);
    }

    /**
     * @return array
     */
    public function getDumpConfigDataProvider()
    {
        return [
            [
                [
                    'default' => [
                        'general' => [
                            'locale' => [
                                'code' => 'en_US',
                                'timezone' => 'America/Chicago',
                            ],
                        ],
                        'design' => ['theme' => ['theme_id' => 2]],
                    ],
                ],
                [
                    'default' => [
                        'general' => [
                            'locale' => [
                                'code' => 'en_US',
                                'timezone' => 'America/Chicago',
                            ],
                        ],
                        'design' => ['theme' => ['theme_id' => 'frontend/Magento/blank']],
                    ],
                ],
            ],
            [
                [
                    'default' => [
                        'general' => [
                            'locale' => [
                                'code' => 'en_US',
                                'timezone' => 'America/Chicago',
                            ],
                        ],
                    ],
                ],
                [
                    'default' => [
                        'general' => [
                            'locale' => [
                                'code' => 'en_US',
                                'timezone' => 'America/Chicago',
                            ],
                        ],
                    ],
                ],
            ],
            [
                [],[],
            ],
            [
                [
                    'stores' => [
                        'default' => [
                            'design' => ['theme' => ['theme_id' => 3]],
                        ],
                    ],
                ],
                [
                    'stores' => [
                        'default' => [
                            'design' => ['theme' => ['theme_id' => 'frontend/Magento/luma']],
                        ],
                    ],
                ],
            ],
            [
                [
                    'websites' => [
                        'base' => [
                            'design' => ['theme' => ['theme_id' => 3]],
                        ],
                    ],
                ],
                [
                    'websites' => [
                        'base' => [
                            'design' => ['theme' => ['theme_id' => 'frontend/Magento/luma']],
                        ],
                    ],
                ],
            ],
        ];
    }
}
