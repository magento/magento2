<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Code\Generator;


/**
 * Class SearchResultBuilderTest
 */
class GenerateSearchResultsBuilderTest extends \PHPUnit_Framework_TestCase
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
            '\Magento\Framework\Code\Generator\Io',
            [],
            [],
            '',
            false
        );
    }

    /**
     * generate SearchResultBuilder class
     */
    public function testGenerate()
    {
        require_once __DIR__ . '/_files/Sample.php';
        /** @var \Magento\Framework\Api\Code\Generator\SearchResultsBuilder $model */
        $model = $this->getMock(
            'Magento\Framework\Api\Code\Generator\SearchResultsBuilder',
            [
                '_validateData'
            ],
            [
                '\Magento\Framework\Api\Code\Generator\Sample',
                null,
                $this->ioObjectMock,
                null,
                null,
                $this->getMock('Magento\Framework\Filesystem\FileResolver')
            ]
        );
        $sampleSearchResultBuilderCode = file_get_contents(__DIR__ . '/_files/SampleSearchResultsBuilder.txt');
        $this->ioObjectMock->expects($this->once())
            ->method('getResultFileName')
            ->with('\Magento\Framework\Api\Code\Generator\SampleSearchResultsBuilder')
            ->will($this->returnValue('SampleSearchResultsBuilder.php'));
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with('SampleSearchResultsBuilder.php', $sampleSearchResultBuilderCode);

        $model->expects($this->once())
            ->method('_validateData')
            ->will($this->returnValue(true));
        $this->assertEquals('SampleSearchResultsBuilder.php', $model->generate(), implode("\n", $model->getErrors()));
    }
}
