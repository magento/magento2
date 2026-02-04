<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Test\Unit\Model\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Select;
use Magento\MediaGalleryUi\Model\SearchCriteria\CollectionProcessor\FilterProcessor\Directory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Directory filter processor
 */
class DirectoryTest extends TestCase
{
    /**
     * @var Directory
     */
    private $directoryFilterProcessor;

    /**
     * @var AbstractDb|MockObject
     */
    private $collectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var Filter|MockObject
     */
    private $filterMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->directoryFilterProcessor = new Directory($this->createMock(\Psr\Log\LoggerInterface::class));
        $this->collectionMock = $this->createMock(AbstractDb::class);
        $this->selectMock = $this->createMock(Select::class);
        $this->filterMock = $this->createMock(Filter::class);

        $this->collectionMock->method('getSelect')
            ->willReturn($this->selectMock);
    }

    /**
     * Test case-sensitive directory filtering
     *
     * @dataProvider caseSensitiveDirectoryDataProvider
     */
    public function testApplyWithCaseSensitiveDirectoryFiltering(
        string $directoryValue,
        string $expectedRegexPattern
    ): void {
        $this->filterMock->method('getValue')
            ->willReturn($directoryValue);

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with(
                'BINARY path REGEXP ? ',
                $expectedRegexPattern
            );

        $result = $this->directoryFilterProcessor->apply($this->filterMock, $this->collectionMock);
        $this->assertTrue($result);
    }

    /**
     * Test that null filter value is handled correctly
     */
    public function testApplyWithNullFilterValue(): void
    {
        $this->filterMock->method('getValue')
            ->willReturn(null);

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with(
                'BINARY path REGEXP ? ',
                '^/[^\/]*$'
            );

        $result = $this->directoryFilterProcessor->apply($this->filterMock, $this->collectionMock);
        $this->assertTrue($result);
    }

    /**
     * Test that percentage signs are stripped from filter value
     */
    public function testApplyStripsPercentageSigns(): void
    {
        $this->filterMock->method('getValue')
            ->willReturn('Testing%Directory%');

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with(
                'BINARY path REGEXP ? ',
                '^TestingDirectory/[^\/]*$'
            );

        $result = $this->directoryFilterProcessor->apply($this->filterMock, $this->collectionMock);
        $this->assertTrue($result);
    }

    /**
     * Test case-sensitive behavior - uppercase vs lowercase
     */
    public function testCaseSensitiveBehavior(): void
    {
        // Test uppercase directory
        $this->filterMock->method('getValue')
            ->willReturn('TESTING');

        $this->selectMock->expects($this->once())
            ->method('where')
            ->with(
                'BINARY path REGEXP ? ',
                '^TESTING/[^\/]*$'
            );

        $result = $this->directoryFilterProcessor->apply($this->filterMock, $this->collectionMock);
        $this->assertTrue($result);
    }

    /**
     * Data provider for case-sensitive directory filtering tests
     *
     * @return array
     */
    public function caseSensitiveDirectoryDataProvider(): array
    {
        return [
            'lowercase_directory' => [
                'directoryValue' => 'testing',
                'expectedRegexPattern' => '^testing/[^\/]*$'
            ],
            'uppercase_directory' => [
                'directoryValue' => 'TESTING',
                'expectedRegexPattern' => '^TESTING/[^\/]*$'
            ],
            'mixed_case_directory' => [
                'directoryValue' => 'Testing',
                'expectedRegexPattern' => '^Testing/[^\/]*$'
            ],
            'directory_with_numbers' => [
                'directoryValue' => 'Test123',
                'expectedRegexPattern' => '^Test123/[^\/]*$'
            ],
            'directory_with_special_chars' => [
                'directoryValue' => 'Test-Dir_001',
                'expectedRegexPattern' => '^Test-Dir_001/[^\/]*$'
            ],
            'nested_directory_path' => [
                'directoryValue' => 'parent/child',
                'expectedRegexPattern' => '^parent/child/[^\/]*$'
            ],
            'empty_directory' => [
                'directoryValue' => '',
                'expectedRegexPattern' => '^/[^\/]*$'
            ]
        ];
    }
}
