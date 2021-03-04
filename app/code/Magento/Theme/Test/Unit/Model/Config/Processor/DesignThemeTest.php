<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Config\Processor;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Design\Theme\ListInterface;
use Magento\Theme\Model\Config\Processor\DesignTheme;

class DesignThemeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DesignTheme
     */
    private $designTheme;

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
        $this->themeList = $this->getMockBuilder(ListInterface::class)->getMockForAbstractClass();
        $this->prepareThemeMock();

        $this->designTheme = new DesignTheme($this->arrayManager, $this->themeList);
    }

    /**
     * @param array $actualResult
     * @param array $expectedResult
     * @dataProvider getDumpConfigDataProvider
     */
    public function testProcess($actualResult, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->designTheme->process($actualResult));
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
            $themeMock->expects(static::any())->method('getId')->willReturn($themeId);

            $themesMap[] = [$themeFullPath, $themeMock];
        }

        $this->themeList->expects(static::any())->method('getThemeByFullPath')->willReturnMap($themesMap);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
                        'design' => ['theme' => ['theme_id' => 'frontend/Magento/blank']],
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
                        'design' => ['theme' => ['theme_id' => 2]],
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
                            'design' => ['theme' => ['theme_id' => 'frontend/Magento/luma']],
                        ],
                    ],
                ],
                [
                    'stores' => [
                        'default' => [
                            'design' => ['theme' => ['theme_id' => 3]],
                        ],
                    ],
                ],
            ],
            [
                [
                    'websites' => [
                        'base' => [
                            'design' => ['theme' => ['theme_id' => 'frontend/Magento/luma']],
                        ],
                    ],
                ],
                [
                    'websites' => [
                        'base' => [
                            'design' => ['theme' => ['theme_id' => 3]],
                        ],
                    ],
                ],
                [
                    [
                        'websites' => [
                            'base' => [
                                'design' => ['theme' => ['theme_id' => '']],
                            ],
                        ],
                    ],
                    [
                        'websites' => [
                            'base' => [
                                'design' => ['theme' => ['theme_id' => '']],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
