<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ioObjectMock;

    protected function setUp()
    {
        $this->ioObjectMock = $this->getMock(\Magento\Framework\Code\Generator\Io::class, [], [], '', false);
    }

    public function testGenerate()
    {
        require_once __DIR__ . '/_files/Sample.php';
        $model = $this->getMock(
            \Magento\Framework\ObjectManager\Code\Generator\Factory::class,
            ['_validateData'],
            [
                \Magento\Framework\ObjectManager\Code\Generator\Sample::class,
                null,
                $this->ioObjectMock,
                null,
                null,
                $this->getMock(\Magento\Framework\Filesystem\FileResolver::class)
            ]
        );

        $this->ioObjectMock->expects($this->once())->method('generateResultFileName')
            ->with('\\' . \Magento\Framework\ObjectManager\Code\Generator\SampleFactory::class)
            ->will($this->returnValue('sample_file.php'));
        $factoryCode = file_get_contents(__DIR__ . '/_files/SampleFactory.txt');
        $this->ioObjectMock->expects($this->once())->method('writeResultFile')
            ->with('sample_file.php', $factoryCode);

        $model->expects($this->once())->method('_validateData')->will($this->returnValue(true));
        $this->assertEquals('sample_file.php', $model->generate());
    }
}
