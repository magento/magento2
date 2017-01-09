<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\Test\Unit\Code\Generator;

/**
 * Class MapperTest
 */
class GenerateMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ioObjectMock;

    /**
     * Prepare test env
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
     * Create mock for class \Magento\Framework\Code\Generator\Io
     */
    public function testGenerate()
    {
        require_once __DIR__ . '/Sample.php';
        $model = $this->getMock(
            \Magento\Framework\Api\Code\Generator\Mapper::class,
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
        $sampleMapperCode = file_get_contents(__DIR__ . '/_files/SampleMapper.txt');
        $this->ioObjectMock->expects($this->once())
            ->method('generateResultFileName')
            ->with('\\' . \Magento\Framework\Api\Code\Generator\SampleMapper::class)
            ->will($this->returnValue('SampleMapper.php'));
        $this->ioObjectMock->expects($this->once())
            ->method('writeResultFile')
            ->with('SampleMapper.php', $sampleMapperCode);

        $model->expects($this->once())
            ->method('_validateData')
            ->will($this->returnValue(true));
        $this->assertEquals('SampleMapper.php', $model->generate());
    }
}
