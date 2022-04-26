<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Test\Unit\Plugin;

use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionStartChecker;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GraphQl\Model\Config\DisableSession;
use Magento\GraphQl\Plugin\DisableSession as DisableSessionPlugin;
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
        $this->sessionStartCheckerMock = $this->createMock(SessionStartChecker::class);
        $this->model = (new ObjectManager($this))->getObject(
            DisableSessionPlugin::class,
            [
                'disableSessionConfig' => $this->disableSessionConfigMock,
                'appState' => $this->appStateMock
            ]
        );
    }

    /**
     * Test afterCheck plugin result over original method result.
     *
     * @param string $area
     * @param bool $config
     * @param bool $methodResult
     * @param bool $expectedResult
     * @return void
     * @dataProvider testAfterCheckDataProvider
     */
    public function testAfterCheck(string $area, bool $config, bool $methodResult, bool $expectedResult)
    {
        $this->disableSessionConfigMock->expects($this->any())->method('isDisabled')->willReturn($config);
        $this->appStateMock->expects($this->any())->method('getAreaCode')->willReturn($area);
        $this->assertEquals($expectedResult, $this->model->afterCheck($this->sessionStartCheckerMock, $methodResult));
    }

    /**
     * Data provider for testAfterCheck.
     *
     * @return array[]
     */
    public function testAfterCheckDataProvider()
    {
        return [
            ['area' => 'graphql', 'config' => true, 'methodResult' =>  false, 'expected' => false],
            ['area' => 'graphql', 'config' => true, 'methodResult' =>  true, 'expected' => false],
            ['area' => 'graphql', 'config' => false, 'methodResult' =>  true, 'expected' => true],
            ['area' => 'graphql', 'config' => false, 'methodResult' =>  false, 'expected' => false],
            ['area' => 'other', 'config' => false, 'methodResult' =>  false, 'expected' => false],
            ['area' => 'other', 'config' => true, 'methodResult' =>  false, 'expected' => false],
            ['area' => 'other', 'config' => true, 'methodResult' =>  true, 'expected' => true],
            ['area' => 'other', 'config' => false, 'methodResult' =>  true, 'expected' => true],
        ];
    }

    /**
     * Test afterCheck plugin result over original method result when no area code set.
     *
     * @param bool $config
     * @param bool $methodResult
     * @param bool $expectedResult
     * @return void
     * @dataProvider testAfterCheckDataProviderNoAreaCode
     */
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
    public function testAfterCheckDataProviderNoAreaCode()
    {
        return [
            ['config' => true, 'methodResult' =>  true, 'expected' => true],
            ['config' => true, 'methodResult' =>  false, 'expected' => false],
            ['config' => false, 'methodResult' =>  true, 'expected' => true],
            ['config' => false, 'methodResult' =>  false, 'expected' => false],
        ];
    }
}
