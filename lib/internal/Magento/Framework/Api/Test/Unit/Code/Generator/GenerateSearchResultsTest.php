<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\Code\Generator;

use Magento\Framework\Api\Code\Generator\SearchResults;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Filesystem\FileResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateSearchResultsTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $ioObjectMock;

    /**
     * Create mock for class \Magento\Framework\Code\Generator\Io
     */
    protected function setUp(): void
    {
        $this->ioObjectMock = $this->createMock(Io::class);
    }

    /**
     * Generate SearchResult class
     */
    public function testGenerate()
    {
        require_once __DIR__ . '/Sample.php';
        $model = $this->getMockBuilder(SearchResults::class)
            ->onlyMethods(['_validateData'])
            ->setConstructorArgs(
                [\Magento\Framework\Api\Code\Generator\Sample::class,
                    null,
                    $this->ioObjectMock,
                    null,
                    null,
                    $this->createMock(FileResolver::class)
                ]
            )
            ->getMock();
        $sampleSearchResultBuilderCode = file_get_contents(__DIR__ . '/_files/SampleSearchResults.txt');
        $this->ioObjectMock->expects($this->once())
            ->method('generateResultFileName')
            ->with('\\' . \Magento\Framework\Api\Code\Generator\SampleSearchResults::class)
            ->willReturn('SampleSearchResults.php');
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with('SampleSearchResults.php', $sampleSearchResultBuilderCode);

        $model->expects($this->once())
            ->method('_validateData')
            ->willReturn(true);
        $this->assertEquals('SampleSearchResults.php', $model->generate());
    }
}
