<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure;

use Magento\Config\Model\Config\Structure\ProductionVisibility;
use Magento\Framework\App\State;

class ProductionVisibilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * @var ProductionVisibility
     */
    private $model;

    protected function setUp()
    {
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configs = [
            'first/path' => ProductionVisibility::DISABLED,
            'second/path' => ProductionVisibility::HIDDEN,
        ];

        $this->model = new ProductionVisibility($this->stateMock, $configs);
    }

    /**
     * @param string $path
     * @param string $mageMode
     * @param bool $expectedResult
     * @dataProvider disabledDataProvider
     */
    public function testIsDisabled($path, $mageMode, $expectedResult)
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mageMode);
        $this->assertSame($expectedResult, $this->model->isDisabled($path));
    }

    /**
     * @return array
     */
    public function disabledDataProvider()
    {
        return [
            ['first/path', State::MODE_PRODUCTION, true],
            ['first/path', State::MODE_DEFAULT, false],
            ['some/path', State::MODE_PRODUCTION, false],
            ['second/path', State::MODE_PRODUCTION, false],
        ];
    }

    /**
     * @param string $path
     * @param string $mageMode
     * @param bool $expectedResult
     * @dataProvider hiddenDataProvider
     */
    public function testIsHidden($path, $mageMode, $expectedResult)
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mageMode);
        $this->assertSame($expectedResult, $this->model->isHidden($path));
    }

    /**
     * @return array
     */
    public function hiddenDataProvider()
    {
        return [
            ['first/path', State::MODE_PRODUCTION, false],
            ['first/path', State::MODE_DEFAULT, false],
            ['some/path', State::MODE_PRODUCTION, false],
            ['second/path', State::MODE_PRODUCTION, true],
            ['second/path', State::MODE_DEVELOPER, false],
        ];
    }
}
