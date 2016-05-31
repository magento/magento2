<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\App;

class IndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\App\Indexer
     */
    protected $entryPoint;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\Processor
     */
    protected $processor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Console\Response
     */
    protected $_response;

    protected function setUp()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', ['getDirectoryWrite'], [], '', false);
        $this->processor = $this->getMock('Magento\Indexer\Model\Processor', [], [], '', false);
        $this->_response = $this->getMock(
            'Magento\Framework\App\Console\Response',
            ['setCode', 'getCode'],
            [],
            '',
            false
        );

        $this->entryPoint = new \Magento\Indexer\App\Indexer('reportDir', $this->filesystem, $this->processor, $this->_response);
    }

    /**
     * @param bool $isExist
     * @param array $callCount
     * @dataProvider executeProvider
     */
    public function testExecute($isExist, $callCount)
    {
        $this->_response->expects($this->once())->method('setCode')->with(0);
        $this->_response->expects($this->once())->method('getCode')->will($this->returnValue(0));
        $dir = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $dir->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $dir->expects($this->once())->method('isExist')->will($this->returnValue($isExist));
        $dir->expects($this->exactly($callCount))->method('delete')->will($this->returnValue(true));
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->will($this->returnValue($dir));
        $this->processor->expects($this->once())->method('reindexAll');
        $this->assertEquals(0, $this->entryPoint->launch()->getCode());
    }

    public function executeProvider(){
        return [
            'set1' => ['isExist' => true, 'expectsValue' => 1],
            'set1' => ['delete' => false, 'expectsValue' => 0]
        ];
    }

    public function testCatchException()
    {
        $bootstrap = $this->getMock('Magento\Framework\App\Bootstrap', [], [], '', false);
        $this->assertFalse($this->entryPoint->catchException($bootstrap, new \Exception()));
    }
}
