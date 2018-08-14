<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

class ProxyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ioObjectMock;

    protected function setUp()
    {
        $this->ioObjectMock = $this->createMock(\Magento\Framework\Code\Generator\Io::class);
    }

    public function testGenerate()
    {
        require_once __DIR__ . '/_files/Sample.php';
        $model = $this->getMockBuilder(\Magento\Framework\ObjectManager\Code\Generator\Proxy::class)
            ->setMethods(['_validateData'])
            ->setConstructorArgs(
                [
                \Magento\Framework\ObjectManager\Code\Generator\Sample::class,
                    null,
                    $this->ioObjectMock,
                    null,
                    null,
                    $this->createMock(\Magento\Framework\Filesystem\FileResolver::class)
                ]
            )
            ->getMock();
        $sampleProxyCode = file_get_contents(__DIR__ . '/_files/SampleProxy.txt');

        $this->ioObjectMock->expects($this->once())->method('generateResultFileName')
            ->with('\\' . \Magento\Framework\ObjectManager\Code\Generator\Sample_Proxy::class)
            ->will($this->returnValue('sample_file.php'));
        $this->ioObjectMock->expects($this->once())->method('writeResultFile')
            ->with('sample_file.php', $sampleProxyCode);

        $model->expects($this->once())->method('_validateData')->will($this->returnValue(true));
        $this->assertEquals('sample_file.php', $model->generate());
    }
}
