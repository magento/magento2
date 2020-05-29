<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure;

use Magento\Config\Model\Config\Structure\ConcealInProductionConfigList;
use Magento\Framework\App\State;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @deprecated Original class has changed the location
 * @see \Magento\Config\Model\Config\Structure\ElementVisibility\ConcealInProduction
 * @see \Magento\Config\Test\Unit\Model\Config\Structure\ElementVisibility\ConcealInProductionTest
 */
class ConcealInProductionConfigListTest extends TestCase
{
    /**
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * @var ConcealInProductionConfigList
     */
    private $model;

    protected function setUp(): void
    {
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configs = [
            'first/path' => ConcealInProductionConfigList::DISABLED,
            'second/path' => ConcealInProductionConfigList::HIDDEN,
            'third' => ConcealInProductionConfigList::DISABLED,
            'third/path' => 'no',
            'third/path/field' => ConcealInProductionConfigList::DISABLED,
            'first/path/field' => 'no',
        ];

        $this->model = new ConcealInProductionConfigList($this->stateMock, $configs);
    }

    /**
     * @param string $path
     * @param string $mageMode
     * @param bool $expectedResult
     * @dataProvider disabledDataProvider
     *
     * @deprecated
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
     *
     * @deprecated
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
     *
     * @deprecated
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
     *
     * @deprecated
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
