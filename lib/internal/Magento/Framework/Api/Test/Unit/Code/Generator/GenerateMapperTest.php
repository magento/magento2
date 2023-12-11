<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\Code\Generator;

use Magento\Framework\Api\Code\Generator\Mapper;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Filesystem\FileResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateMapperTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $ioObjectMock;

    /**
     * Prepare test env
     */
    protected function setUp(): void
    {
        $this->ioObjectMock = $this->createMock(Io::class);
    }

    /**
     * Create mock for class \Magento\Framework\Code\Generator\Io
     */
    public function testGenerate()
    {
        require_once __DIR__ . '/Sample.php';
        $model = $this->getMockBuilder(Mapper::class)
            ->setMethods(['_validateData'])
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
