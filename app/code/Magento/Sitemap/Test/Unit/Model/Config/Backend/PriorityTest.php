<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Unit\Model\Config\Backend;

use Magento\Sitemap\Model\Config\Backend\Priority;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for @see Priority
 */
class PriorityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Priority|MockObject
     */
    private $priorityMock;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->priorityMock = $this->getMockBuilder(Priority::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
    }

    /**
     * @param string $value
     * @dataProvider dataProviderTestBeforeSaveValueCorrect
     */
    public function testBeforeSave($value)
    {
        $this->priorityMock->expects($this->once())
            ->method('getValue')
            ->willReturn($value);

        $this->assertSame($this->priorityMock, $this->priorityMock->beforeSave());
    }

    /**
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
     * @return array
     */
    public function dataProviderTestBeforeSaveValueCorrect()
    {
        return [
            ['0'], ['0.0'], ['0.5'], ['1']
        ];
    }

    /**
     * @return array
     */
    public function dataProviderTestBeforeSaveValueOutOfRange()
    {
        return [
            ['-1'], ['2'], ['nan']
        ];
    }
}
