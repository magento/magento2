<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Filesystem\FileResolver;
use Magento\Framework\ObjectManager\Code\Generator\Proxy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $ioObjectMock;

    protected function setUp(): void
    {
        $this->ioObjectMock = $this->createMock(Io::class);
    }

    public function testGenerate()
    {
        require_once __DIR__ . '/_files/Sample.php';
        $model = $this->getMockBuilder(Proxy::class)
            ->setMethods(['_validateData'])
            ->setConstructorArgs(
                [
                    \Magento\Framework\ObjectManager\Code\Generator\Sample::class,
                    null,
                    $this->ioObjectMock,
                    null,
                    null,
                    $this->createMock(FileResolver::class)
                ]
            )
            ->getMock();
        $sampleProxyCode = file_get_contents(__DIR__ . '/_files/SampleProxy.txt');

        $this->ioObjectMock->expects($this->once())->method('generateResultFileName')
            ->with('\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample_Proxy::class)
            ->willReturn('sample_file.php');
        $this->ioObjectMock->expects($this->once())->method('writeResultFile')
            ->with('sample_file.php', $sampleProxyCode);

        $model->expects($this->once())->method('_validateData')->willReturn(true);
        $this->assertEquals('sample_file.php', $model->generate());
    }
}
