<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\State;

use \Magento\Framework\App\State\Cleanup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;

class CleanupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cachePool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    private $cache;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var Cleanup
     */
    private $object;

    protected function setUp()
    {
        $this->cachePool = $this->getMock('Magento\Framework\App\Cache\Frontend\Pool', [], [], '', false);
        $this->cache = [
            $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface'),
            $this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface'),
        ];
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->object = new Cleanup($this->cachePool, $this->filesystem);
    }

    public function testClearCaches()
    {
        $this->mockCachePoolIterator();
        $this->cache[0]->expects($this->once())->method('clean');
        $this->cache[1]->expects($this->once())->method('clean');
        $this->object->clearCaches();
    }

    /**
     * Mocks cache pool iteration through 2 items
     *
     * @return void
     */
    private function mockCachePoolIterator()
    {
        $this->cachePool->expects($this->any())->method('valid')->will($this->onConsecutiveCalls(true, true, false));
        $this->cachePool->expects($this->any())
            ->method('current')
            ->will($this->onConsecutiveCalls($this->cache[0], $this->cache[1]));
    }

    public function testClearCodeGeneratedClasses()
    {
        $dir = $this->getDirectoryCleanMock();
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::GENERATION)
            ->willReturn($dir);
        $this->object->clearCodeGeneratedClasses();
    }

    public function testClearMaterializedViewFiles()
    {
        $static = $this->getDirectoryCleanMock();
        $var = $this->getDirectoryCleanMock(DirectoryList::TMP_MATERIALIZATION_DIR);
        $this->filesystem->expects($this->exactly(2))->method('getDirectoryWrite')->will($this->returnValueMap([
            [DirectoryList::STATIC_VIEW, DriverPool::FILE, $static],
            [DirectoryList::VAR_DIR, DriverPool::FILE, $var],
        ]));
        $this->object->clearMaterializedViewFiles();
    }

    /**
     * Gets a mock of directory with expectation to be cleaned
     *
     * @param string|null $subPath
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDirectoryCleanMock($subPath = null)
    {
        $dir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $dir->expects($this->once())->method('search')->with('*', $subPath)->willReturn(['one', 'two']);
        $dir->expects($this->exactly(2))->method('delete');
        return $dir;
    }
}
