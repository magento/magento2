<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure;

use Magento\Config\Model\Config\Structure\ElementVisibilityComposite;
use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ElementVisibilityCompositeTest extends TestCase
{
    /**
     * @var ElementVisibilityComposite
     */
    private $model;

    /**
     * @var ElementVisibilityInterface|MockObject
     */
    private $firstVisibilityMock;

    /**
     * @var ElementVisibilityInterface|MockObject
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
        $this->expectException('Magento\Framework\Exception\ConfigurationMismatchException');
        $this->expectExceptionMessage(sprintf(
            'stdClass: Instance of %s, got stdClass instead',
            'Magento\Config\Model\Config\Structure\ElementVisibilityInterface is expected'
        ));
        $visibility = [
            'stdClass' => new \stdClass()
        ];

        new ElementVisibilityComposite($visibility);
    }

    /**
     * @param InvokedCount $firstExpects
     * @param bool $firstResult
     * @param InvokedCount $secondExpects
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
     * @param InvokedCount $firstExpects
     * @param bool $firstResult
     * @param InvokedCount $secondExpects
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
