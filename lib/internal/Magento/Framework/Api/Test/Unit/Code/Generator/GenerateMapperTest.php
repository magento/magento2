<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\Code\Generator;

/**
 * Class MapperTest
 */
class GenerateMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $ioObjectMock;

    /**
     * Prepare test env
     */
    protected function setUp(): void
    {
        $this->ioObjectMock = $this->createMock(\Magento\Framework\Code\Generator\Io::class);
    }

    /**
     * Create mock for class \Magento\Framework\Code\Generator\Io
     */
    public function testGenerate()
    {
        require_once __DIR__ . '/Sample.php';
        $model = $this->getMockBuilder(\Magento\Framework\Api\Code\Generator\Mapper::class)
            ->setMethods(['_validateData'])
            ->setConstructorArgs(
                [\Magento\Framework\Api\Code\Generator\Sample::class,
                    null,
                    $this->ioObjectMock,
                    null,
                    null,
                    $this->createMock(\Magento\Framework\Filesystem\FileResolver::class)
                ]
            )
            ->getMock();
        $sampleMapperCode = file_get_contents(__DIR__ . '/_files/SampleMapper.txt');
        $this->ioObjectMock->expects($this->once())
            ->method('generateResultFileName')
            ->with('\\' . \Magento\Framework\Api\Code\Generator\SampleMapper::class)
            ->willReturn('SampleMapper.php');
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with('SampleMapper.php', $sampleMapperCode);

        $model->expects($this->once())
            ->method('_validateData')
            ->willReturn(true);
        $this->assertEquals('SampleMapper.php', $model->generate());
    }
}
