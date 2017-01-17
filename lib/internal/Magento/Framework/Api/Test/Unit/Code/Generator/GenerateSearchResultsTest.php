<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\Code\Generator;


/**
 * Class SearchResultTest
 */
class GenerateSearchResultsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ioObjectMock;

    /**
     * Create mock for class \Magento\Framework\Code\Generator\Io
     */
    protected function setUp()
    {
        $this->ioObjectMock = $this->getMock(
            \Magento\Framework\Code\Generator\Io::class,
            [],
            [],
            '',
            false
        );
    }

    /**
     * Generate SearchResult class
     */
    public function testGenerate()
    {
        require_once __DIR__ . '/Sample.php';
        $model = $this->getMock(
            \Magento\Framework\Api\Code\Generator\SearchResults::class,
            [
                '_validateData'
            ],
            [\Magento\Framework\Api\Code\Generator\Sample::class,
                null,
                $this->ioObjectMock,
                null,
                null,
                $this->getMock(\Magento\Framework\Filesystem\FileResolver::class)
            ]
        );
        $sampleSearchResultBuilderCode = file_get_contents(__DIR__ . '/_files/SampleSearchResults.txt');
        $this->ioObjectMock->expects($this->once())
            ->method('generateResultFileName')
            ->with('\\' . \Magento\Framework\Api\Code\Generator\SampleSearchResults::class)
            ->will($this->returnValue('SampleSearchResults.php'));
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with('SampleSearchResults.php', $sampleSearchResultBuilderCode);

        $model->expects($this->once())
            ->method('_validateData')
            ->will($this->returnValue(true));
        $this->assertEquals('SampleSearchResults.php', $model->generate());
    }
}
