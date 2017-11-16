<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale\Test\Unit\Deployed;

use Magento\Framework\App\State;
use Magento\Framework\Locale\AvailableLocalesInterface;
use Magento\Framework\Locale\Deployed\Options;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for Options class.
 *
 * @see Options
 */
class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var AvailableLocalesInterface|MockObject
     */
    private $availableLocalesMock;

    /**
     * @var DesignInterface|MockObject
     */
    private $designMock;

    /**
     * @var ListsInterface|MockObject
     */
    private $localeListsMock;

    /**
     * @var Options
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->availableLocalesMock = $this->getMockBuilder(AvailableLocalesInterface::class)
            ->getMockForAbstractClass();
        $this->designMock = $this->getMockBuilder(DesignInterface::class)
            ->getMockForAbstractClass();
        $this->localeListsMock = $this->getMockBuilder(ListsInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Options(
            $this->localeListsMock,
            $this->stateMock,
            $this->availableLocalesMock,
            $this->designMock
        );
    }

    /**
     * @param string $mode
     * @param array $locales
     * @param array $expectedLocales
     * @param array $deployedCodes
     * @dataProvider getLocaleDataProvider
     */
    public function testGetOptionLocales($mode, $locales, $expectedLocales, $deployedCodes)
    {
        $this->localeListsMock->expects($this->once())
            ->method('getOptionLocales')
            ->willReturn($locales);

        $this->prepareGetLocales($mode, $deployedCodes);

        $this->assertEquals($expectedLocales, array_values($this->model->getOptionLocales()));
    }

    /**
     * @param string $mode
     * @param array $locales
     * @param array $expectedLocales
     * @param array $deployedCodes
     * @dataProvider getLocaleDataProvider
     */
    public function testGetTranslatedOptionLocales($mode, $locales, $expectedLocales, $deployedCodes)
    {
        $this->localeListsMock->expects($this->once())
            ->method('getTranslatedOptionLocales')
            ->willReturn($locales);

        $this->prepareGetLocales($mode, $deployedCodes);

        $this->assertEquals($expectedLocales, array_values($this->model->getTranslatedOptionLocales()));
    }

    /**
     * @param $mode
     * @param $deployedCodes
     * @return void
     */
    private function prepareGetLocales($mode, $deployedCodes)
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mode);

        if ($mode == State::MODE_PRODUCTION) {
            $area = 'area';
            $code = 'code';
            $themeMock = $this->getMockBuilder(ThemeInterface::class)
                ->getMockForAbstractClass();
            $themeMock->expects($this->once())
                ->method('getCode')
                ->willReturn($code);
            $themeMock->expects($this->once())
                ->method('getArea')
                ->willReturn($area);
            $this->designMock->expects($this->once())
                ->method('getDesignTheme')
                ->willReturn($themeMock);
            $this->availableLocalesMock->expects($this->once())
                ->method('getList')
                ->with($code, $area)
                ->willReturn($deployedCodes);
        }
    }

    /**
     * @return array
     */
    public function getLocaleDataProvider()
    {
        return [
            [
                State::MODE_PRODUCTION,
                [
                    [
                        'value' => 'da_DK',
                        'label' => 'Danish (Denmark)'
                    ],
                    [
                        'value' => 'de_DE',
                        'label' => 'German (German)'
                    ]
                ],
                [
                    [
                        'value' => 'de_DE',
                        'label' => 'German (German)'
                    ]
                ],
                [
                    'de_DE'
                ]
            ],
            [
                State::MODE_PRODUCTION,
                [
                    [
                        'value' => 'de_DE',
                        'label' => 'German (German)'
                    ]
                ],
                [],
                []
            ],
            [
                State::MODE_DEVELOPER,
                [
                    [
                        'value' => 'da_DK',
                        'label' => 'Danish (Denmark)'
                    ],
                    [
                        'value' => 'de_DE',
                        'label' => 'German (German)'
                    ]
                ],
                [
                    [
                        'value' => 'da_DK',
                        'label' => 'Danish (Denmark)'
                    ],
                    [
                        'value' => 'de_DE',
                        'label' => 'German (German)'
                    ]
                ],
                [
                    'de_DE'
                ]
            ],
        ];
    }
}
