<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\Config\Backend;

use Magento\Sitemap\Model\Config\Backend\Priority;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see Priority
 */
class PriorityTest extends TestCase
{
    /**
     * @var Priority|MockObject
     */
    private $priorityMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->priorityMock = $this->getMockBuilder(Priority::class)
            ->disableOriginalConstructor()
            ->addMethods(['getValue'])
            ->getMock();
    }

    /**
     * Verify before save in chainable
     *
     * @param string $value
     * @dataProvider dataProviderTestBeforeSaveValueCorrect
     */
    public function testBeforeSaveIsChainable($value)
    {
        $this->priorityMock->expects($this->once())
            ->method('getValue')
            ->willReturn($value);

        $this->assertSame($this->priorityMock, $this->priorityMock->beforeSave());
    }

    /**
     * Verify before save value out of range
     *
     * @param string $value
     * @dataProvider dataProviderTestBeforeSaveValueOutOfRange
     */
    public function testBeforeSaveValueOutOfRange($value)
    {
        $this->priorityMock->expects($this->once())
            ->method('getValue')
            ->willReturn($value);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The priority must be between 0 and 1.');

        $this->priorityMock->beforeSave();
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function dataProviderTestBeforeSaveValueCorrect()
    {
        return [
            ['0'], ['0.0'], ['0.5'], ['1']
        ];
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function dataProviderTestBeforeSaveValueOutOfRange()
    {
        return [
            ['-1'], ['2'], ['nan']
        ];
    }
}
