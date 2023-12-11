<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\App;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Console\Response;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Indexer\App\Indexer;
use Magento\Indexer\Model\Processor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexerTest extends TestCase
{
    /**
     * @var Indexer
     */
    protected $entryPoint;

    /**
     * @var MockObject|Processor
     */
    protected $processor;

    /**
     * @var MockObject|Filesystem
     */
    protected $filesystem;

    /**
     * @var MockObject|Response
     */
    protected $_response;

    protected function setUp(): void
    {
        $this->filesystem = $this->createPartialMock(Filesystem::class, ['getDirectoryWrite']);
        $this->processor = $this->createMock(Processor::class);
        $this->_response = $this->getMockBuilder(Response::class)
            ->addMethods(['getCode'])
            ->onlyMethods(['setCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->entryPoint = new Indexer(
            'reportDir',
            $this->filesystem,
            $this->processor,
            $this->_response
        );
    }

    /**
     * @param bool $isExist
     * @param array $callCount
     * @dataProvider executeProvider
     */
    public function testExecute($isExist, $callCount)
    {
        $this->_response->expects($this->once())->method('setCode')->with(0);
        $this->_response->expects($this->once())->method('getCode')->willReturn(0);
        $dir = $this->createMock(Write::class);
        $dir->expects($this->any())->method('getRelativePath')->willReturnArgument(0);
        $dir->expects($this->once())->method('isExist')->willReturn($isExist);
        $dir->expects($this->exactly($callCount))->method('delete')->willReturn(true);
        $this->filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($dir);
        $this->processor->expects($this->once())->method('reindexAll');
        $this->assertEquals(0, $this->entryPoint->launch()->getCode());
    }

    /**
     * @return array
     */
    public function executeProvider()
    {
        return [
            'set1' => ['isExist' => true, 'expectsValue' => 1],
            'set2' => ['delete' => false, 'expectsValue' => 0]
        ];
    }

    public function testCatchException()
    {
        $bootstrap = $this->createMock(Bootstrap::class);
        $this->assertFalse($this->entryPoint->catchException($bootstrap, new \Exception()));
    }
}
