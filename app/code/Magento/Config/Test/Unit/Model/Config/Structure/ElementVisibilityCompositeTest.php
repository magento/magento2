<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure;

use Magento\Config\Model\Config\Structure\ElementVisibilityComposite;
use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;

class ElementVisibilityCompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ElementVisibilityComposite
     */
    private $model;

    /**
     * @var ElementVisibilityInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $firstVisibilityMock;

    /**
     * @var ElementVisibilityInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $secondVisibilityMock;

    protected function setUp(): void
    {
        $this->firstVisibilityMock = $this->getMockBuilder(ElementVisibilityInterface::class)
            ->getMockForAbstractClass();
        $this->secondVisibilityMock = $this->getMockBuilder(ElementVisibilityInterface::class)
            ->getMockForAbstractClass();

        $this->model = new ElementVisibilityComposite([$this->firstVisibilityMock, $this->secondVisibilityMock]);
    }

    /**
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     */
    public function testException()
    {
        $this->expectException(\Magento\Framework\Exception\ConfigurationMismatchException::class);
        $this->expectExceptionMessage('stdClass: Instance of Magento\\Config\\Model\\Config\\Structure\\ElementVisibilityInterface is expected, got stdClass instead');

        $visibility = [
            'stdClass' => new \stdClass()
        ];

        new ElementVisibilityComposite($visibility);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $firstExpects
     * @param bool $firstResult
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $secondExpects
     * @param bool $secondResult
     * @param bool $expectedResult
     * @dataProvider visibilityDataProvider
     */
    public function testDisabled($firstExpects, $firstResult, $secondExpects, $secondResult, $expectedResult)
    {
        $path = 'some/path';
        $this->firstVisibilityMock->expects($firstExpects)
            ->method('isDisabled')
            ->with($path)
            ->willReturn($firstResult);
        $this->secondVisibilityMock->expects($secondExpects)
            ->method('isDisabled')
            ->with($path)
            ->willReturn($secondResult);

        $this->assertSame($expectedResult, $this->model->isDisabled($path));
    }

    /**
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $firstExpects
     * @param bool $firstResult
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $secondExpects
     * @param bool $secondResult
     * @param bool $expectedResult
     * @dataProvider visibilityDataProvider
     */
    public function testHidden($firstExpects, $firstResult, $secondExpects, $secondResult, $expectedResult)
    {
        $path = 'some/path';
        $this->firstVisibilityMock->expects($firstExpects)
            ->method('isHidden')
            ->with($path)
            ->willReturn($firstResult);
        $this->secondVisibilityMock->expects($secondExpects)
            ->method('isHidden')
            ->with($path)
            ->willReturn($secondResult);

        $this->assertSame($expectedResult, $this->model->isHidden($path));
    }

    /**
     * @return array
     */
    public function visibilityDataProvider()
    {
        return [
            [$this->once(), false, $this->once(), false, false],
            [$this->once(), false, $this->once(), true, true],
            [$this->once(), true, $this->never(), true, true],
            [$this->once(), true, $this->never(), false, true],
        ];
    }
}
