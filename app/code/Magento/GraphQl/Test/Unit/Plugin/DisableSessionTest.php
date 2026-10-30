<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Test\Unit\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionStartChecker;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GraphQl\Model\Config\DisableSession;
use Magento\GraphQl\Plugin\DisableSession as DisableSessionPlugin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for DisableSession plugin.
 */
class DisableSessionTest extends TestCase
{
    /**
     * @var DisableSession|MockObject
     */
    private $disableSessionConfigMock;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var DisableSessionPlugin
     */
    private $model;

    /**
     * @var SessionStartChecker|MockObject
     */
    private $sessionStartCheckerMock;

    public function setUp(): void
    {
        $this->disableSessionConfigMock = $this->createMock(DisableSession::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->sessionStartCheckerMock = $this->createMock(SessionStartChecker::class);
        $this->model = (new ObjectManager($this))->getObject(
            DisableSessionPlugin::class,
            [
                'disableSessionConfig' => $this->disableSessionConfigMock,
                'appState' => $this->appStateMock,
                'request' => $this->requestMock
            ]
        );
    }

    /**
     * Test afterCheck plugin result over original method result.
     *
     * @param string $area
     * @param bool $config
     * @param bool $isGet
     * @param bool $methodResult
     * @param bool $expectedResult
     * @return void
     */
    #[DataProvider('afterCheckDataProvider')]
    public function testAfterCheck(
        string $area,
        bool $config,
        bool $isGet,
        bool $methodResult,
        bool $expectedResult
    ): void {
        $this->disableSessionConfigMock->expects($this->any())->method('isDisabled')->willReturn($config);
        $this->appStateMock->expects($this->any())->method('getAreaCode')->willReturn($area);
        $this->requestMock->expects($this->any())->method('isGet')->willReturn($isGet);
        $this->assertEquals($expectedResult, $this->model->afterCheck($this->sessionStartCheckerMock, $methodResult));
    }

    /**
     * Data provider for testAfterCheck.
     *
     * @return array[]
     */
    public static function afterCheckDataProvider()
    {
        return [
            [
                'area' => 'graphql', 'config' => true, 'isGet' => false,
                'methodResult' => false, 'expectedResult' => false
            ],
            [
                'area' => 'graphql', 'config' => true, 'isGet' => false,
                'methodResult' => true, 'expectedResult' => false
            ],
            [
                'area' => 'graphql', 'config' => false, 'isGet' => false,
                'methodResult' => true, 'expectedResult' => true
            ],
            [
                'area' => 'graphql', 'config' => false, 'isGet' => true,
                'methodResult' => true, 'expectedResult' => false
            ],
            [
                'area' => 'graphql', 'config' => false, 'isGet' => true,
                'methodResult' => false, 'expectedResult' => false
            ],
            [
                'area' => 'other', 'config' => false, 'isGet' => true,
                'methodResult' => true, 'expectedResult' => true
            ],
            [
                'area' => 'other', 'config' => true, 'isGet' => true,
                'methodResult' => true, 'expectedResult' => true
            ],
            [
                'area' => 'other', 'config' => false, 'isGet' => false,
                'methodResult' => false, 'expectedResult' => false
            ],
        ];
    }

    /**
     * Test afterCheck plugin result over original method result when no area code set.
     *
     * @param bool $config
     * @param bool $methodResult
     * @param bool $expectedResult
     * @return void
     */
    #[DataProvider('afterCheckDataProviderNoAreaCode')]
    public function testAfterCheckNoArea(bool $config, bool $methodResult, bool $expectedResult)
    {
        $this->disableSessionConfigMock->expects($this->any())->method('isDisabled')->willReturn($config);
        $this->appStateMock->expects($this->any())
            ->method('getAreaCode')
            ->willThrowException(new LocalizedException(__('Are code not set')));
        $this->assertEquals($expectedResult, $this->model->afterCheck($this->sessionStartCheckerMock, $methodResult));
    }

    /**
     * Data provider for testAfterCheck.
     *
     * @return array[]
     */
    public static function afterCheckDataProviderNoAreaCode()
    {
        return [
            ['config' => true, 'methodResult' =>  true, 'expectedResult' => true],
            ['config' => true, 'methodResult' =>  false, 'expectedResult' => false],
            ['config' => false, 'methodResult' =>  true, 'expectedResult' => true],
            ['config' => false, 'methodResult' =>  false, 'expectedResult' => false],
        ];
    }
}
