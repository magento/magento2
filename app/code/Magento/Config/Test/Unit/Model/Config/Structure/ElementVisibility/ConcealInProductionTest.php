<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\ElementVisibility;

use Magento\Config\Model\Config\Structure\ElementVisibility\ConcealInProduction;
use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;
use Magento\Framework\App\State;

class ConcealInProductionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * @var ConcealInProduction
     */
    private $model;

    protected function setUp()
    {
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configs = [
            'first/path' => ElementVisibilityInterface::DISABLED,
            'second/path' => ElementVisibilityInterface::HIDDEN,
            'third' => ElementVisibilityInterface::DISABLED,
            'third/path' => 'no',
            'third/path/field' => ElementVisibilityInterface::DISABLED,
            'first/path/field' => 'no',
            'fourth' => ElementVisibilityInterface::HIDDEN,
        ];
        $exemptions = [
            'fourth/path/value' => '',
        ];

        $this->model = new ConcealInProduction($this->stateMock, $configs, $exemptions);
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
            ['first/path/field', State::MODE_PRODUCTION, false],
            ['first/path/field2', State::MODE_PRODUCTION, true],
            ['first/path', State::MODE_DEFAULT, false],
            ['some/path', State::MODE_PRODUCTION, false],
            ['second/path', State::MODE_PRODUCTION, false],
            ['third', State::MODE_PRODUCTION, true],
            ['third/path2', State::MODE_PRODUCTION, true],
            ['third/path2/field', State::MODE_PRODUCTION, true],
            ['third/path', State::MODE_PRODUCTION, false],
            ['third/path/field', State::MODE_PRODUCTION, true],
            ['third/path/field2', State::MODE_PRODUCTION, false],
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
            ['second/path/field', State::MODE_PRODUCTION, true],
            ['second/path', State::MODE_DEVELOPER, false],
            ['fourth/path/value', State::MODE_PRODUCTION, false],
            ['fourth/path/test', State::MODE_PRODUCTION, true],
        ];
    }
}
