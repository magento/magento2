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
    private const MIXED_TYPE_PHP_VERSION = '8.0.0';

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
            ->onlyMethods(['_validateData'])
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

    public function testGenerateMixedType()
    {
        if (version_compare(PHP_VERSION, self::MIXED_TYPE_PHP_VERSION) < 0) {
            $this->markTestSkipped('This test requires at least PHP version ' . self::MIXED_TYPE_PHP_VERSION);
        }

        require_once __DIR__ . '/_files/SampleMixed.php';
        $model = $this->getMockBuilder(Proxy::class)
            ->onlyMethods(['_validateData'])
            ->setConstructorArgs(
                [
                    \Magento\Framework\ObjectManager\Code\Generator\SampleMixed::class,
                    null,
                    $this->ioObjectMock,
                    null,
                    null,
                    $this->createMock(FileResolver::class)
                ]
            )
            ->getMock();
        $sampleMixedProxyCode = file_get_contents(__DIR__ . '/_files/SampleMixedProxy.txt');

        $this->ioObjectMock->expects($this->once())->method('generateResultFileName')
            ->with('\\' . \Magento\Framework\ObjectManager\Code\Generator\SampleMixed_Proxy::class)
            ->willReturn('sample_mixed_file.php');
        $this->ioObjectMock->expects($this->once())->method('writeResultFile')
            ->with('sample_mixed_file.php', $sampleMixedProxyCode);

        $model->expects($this->once())->method('_validateData')->willReturn(true);
        $this->assertEquals('sample_mixed_file.php', $model->generate());
    }
}
