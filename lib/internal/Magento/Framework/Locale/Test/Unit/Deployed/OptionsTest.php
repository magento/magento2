<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale\Test\Unit\Deployed;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Config\ConfigOptionsListConstants as Constants;
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
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

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
        $this->deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);

        $this->model = new Options(
            $this->localeListsMock,
            $this->stateMock,
            $this->availableLocalesMock,
            $this->designMock,
            $this->deploymentConfigMock
        );
    }

    /**
     * @param string $mode
     * @param int $scdOnDemand
     * @param array $locales
     * @return void
     *
     * @dataProvider getFullLocalesDataProvider
     */
    public function testGetOptionLocalesFull(string $mode, int $scdOnDemand, array $locales)
    {
        $this->localeListsMock->expects($this->once())
            ->method('getOptionLocales')
            ->willReturn($locales);

        $this->prepareGetLocalesFull($mode, $scdOnDemand);

        $this->assertEquals($locales, array_values($this->model->getOptionLocales()));
    }

    /**
     * @param string $mode
     * @param int $scdOnDemand
     * @param array $locales
     * @return void
     *
     * @dataProvider getFullLocalesDataProvider
     */
    public function testGetTranslatedOptionLocalesFull(string $mode, int $scdOnDemand, array $locales)
    {
        $this->localeListsMock->expects($this->once())
            ->method('getTranslatedOptionLocales')
            ->willReturn($locales);

        $this->prepareGetLocalesFull($mode, $scdOnDemand);

        $this->assertEquals($locales, array_values($this->model->getTranslatedOptionLocales()));
    }

    /**
     * @param string $mode
     * @param int $scdOnDemand
     * @param array $locales
     * @param array $expectedLocales
     * @param array $deployedCodes
     * @return void
     *
     * @dataProvider getLimitedLocalesDataProvider
     */
    public function testGetOptionLocalesLimited(
        string $mode,
        int $scdOnDemand,
        array $locales,
        array $expectedLocales,
        array $deployedCodes
    ) {
        $this->localeListsMock->expects($this->once())
            ->method('getOptionLocales')
            ->willReturn($locales);

        $this->prepareGetLocalesLimited($mode, $scdOnDemand, $deployedCodes);

        $this->assertEquals($expectedLocales, array_values($this->model->getOptionLocales()));
    }

    /**
     * @param string $mode
     * @param int $scdOnDemand
     * @param array $locales
     * @param array $expectedLocales
     * @param array $deployedCodes
     * @return void
     *
     * @dataProvider getLimitedLocalesDataProvider
     */
    public function testGetTranslatedOptionLocalesLimited(
        string $mode,
        int $scdOnDemand,
        array $locales,
        array $expectedLocales,
        array $deployedCodes
    ) {
        $this->localeListsMock->expects($this->once())
            ->method('getTranslatedOptionLocales')
            ->willReturn($locales);

        $this->prepareGetLocalesLimited($mode, $scdOnDemand, $deployedCodes);

        $this->assertEquals($expectedLocales, array_values($this->model->getTranslatedOptionLocales()));
    }

    /**
     * @param string $mode
     * @param int $scdOnDemand
     * @param array $deployedCodes
     * @return void
     */
    private function prepareGetLocalesLimited(string $mode, int $scdOnDemand, $deployedCodes)
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mode);
        $this->deploymentConfigMock->expects($this->any())
            ->method('getConfigData')
            ->with(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn($scdOnDemand);

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

    /**
     * @param string $mode
     * @param int $scdOnDemand
     * @return void
     */
    private function prepareGetLocalesFull(string $mode, int $scdOnDemand)
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mode);
        $this->deploymentConfigMock->expects($this->any())
            ->method('getConfigData')
            ->with(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)
            ->willReturn($scdOnDemand);

        $this->designMock->expects($this->never())
            ->method('getDesignTheme');
    }

    /**
     * @return array
     */
    public function getFullLocalesDataProvider(): array
    {
        $deLocale = [
            'value' => 'de_DE',
            'label' => 'German (German)'
        ];
        $daLocale = [
            'value' => 'da_DK',
            'label' => 'Danish (Denmark)'
        ];

        return [
            [
                State::MODE_PRODUCTION,
                1,
                [$daLocale, $deLocale],
            ],
            [
                State::MODE_DEVELOPER,
                0,
                [$daLocale, $deLocale],
            ],
            [
                State::MODE_DEVELOPER,
                1,
                [$deLocale],
            ],
            [
                State::MODE_DEFAULT,
                0,
                [$daLocale],
            ],
            [
                State::MODE_DEFAULT,
                1,
                [$daLocale, $deLocale],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getLimitedLocalesDataProvider(): array
    {
        $deLocale = [
            'value' => 'de_DE',
            'label' => 'German (German)'
        ];
        $daLocale = [
            'value' => 'da_DK',
            'label' => 'Danish (Denmark)'
        ];

        return [
            [
                State::MODE_PRODUCTION,
                0,
                [
                    $daLocale,
                    $deLocale
                ],
                [
                    $deLocale
                ],
                [
                    'de_DE'
                ]
            ],
            [
                State::MODE_PRODUCTION,
                0,
                [
                    $daLocale,
                    $deLocale
                ],
                [
                    $daLocale,
                    $deLocale
                ],
                [
                    'da_DK',
                    'de_DE'
                ]
            ],
        ];
    }
}
