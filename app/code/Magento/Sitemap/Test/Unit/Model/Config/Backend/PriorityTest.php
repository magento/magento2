<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Test\Unit\Model\Config\Backend;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Sitemap\Model\Config\Backend\Priority;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see Priority
 */
class PriorityTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Priority|MockObject
     */
    private $priorityMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->priorityMock = $this->createPartialMockWithReflection(
            Priority::class,
            ['getValue']
        );
    }

    /**
     * Verify before save in chainable
     *
     * @param string $value
     */
    #[DataProvider('dataProviderTestBeforeSaveValueCorrect')]
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
     */
    #[DataProvider('dataProviderTestBeforeSaveValueOutOfRange')]
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
