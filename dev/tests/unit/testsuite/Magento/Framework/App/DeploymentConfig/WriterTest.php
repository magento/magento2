<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\App\Filesystem\DirectoryList;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Writer
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $reader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dirWrite;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formatter;

    protected function setUp()
    {
        $this->reader = $this->getMock('Magento\Framework\App\DeploymentConfig\Reader', [], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->formatter = $this->getMockForAbstractClass(
            'Magento\Framework\App\DeploymentConfig\Writer\FormatterInterface'
        );
        $this->object = new Writer($this->reader, $filesystem, $this->formatter);
        $this->reader->expects($this->any())->method('getFile')->willReturn('test.php');
        $this->dirWrite = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::CONFIG)
            ->willReturn($this->dirWrite);
    }

    public function testCreate()
    {
        $segments = [
            $this->createSegment('foo', 'bar'),
            $this->createSegment('baz', ['value1', 'value2']),
        ];
        $expected = ['foo' => 'bar', 'baz' => ['value1', 'value2']];
        $this->formatter->expects($this->once())->method('format')->with($expected)->willReturn('formatted');
        $this->dirWrite->expects($this->once())->method('writeFile')->with('test.php', 'formatted');
        $this->object->create($segments);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage An instance of SegmentInterface is expected
     */
    public function testCreateException()
    {
        $this->object->create(['some_bogus_data']);
    }

    public function testUpdate()
    {
        $segment = $this->createSegment('key', ['nested_key' => 'value']);
        $preExisting = ['foo' => 'bar', 'key' => 'value', 'baz' => 1];
        $this->reader->expects($this->once())->method('load')->willReturn($preExisting);
        $expected = ['foo' => 'bar', 'key' => ['nested_key' => 'value'], 'baz' => 1];
        $this->formatter->expects($this->once())->method('format')->with($expected)->willReturn('formatted');
        $this->dirWrite->expects($this->once())->method('writeFile')->with('test.php', 'formatted');
        $this->object->update($segment);
    }

    /**
     * Creates a segment mock
     *
     * @param string $key
     * @param mixed $data
     * @return SegmentInterface
     */
    private function createSegment($key, $data)
    {
        $result = $this->getMockForAbstractClass('Magento\Framework\App\DeploymentConfig\SegmentInterface');
        $result->expects($this->atLeastOnce())->method('getKey')->willReturn($key);
        $result->expects($this->atLeastOnce())->method('getData')->willReturn($data);
        return $result;
    }
}
