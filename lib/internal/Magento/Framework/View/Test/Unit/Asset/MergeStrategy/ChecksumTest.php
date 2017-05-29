<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset\MergeStrategy;

use \Magento\Framework\View\Asset\MergeStrategy\Checksum;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\Source;

class ChecksumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\MergeStrategyInterface
     */
    private $mergerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\ReadInterface
     */
    private $sourceDir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $targetDir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Asset\File
     */
    private $resultAsset;

    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetSource;

    /**
     * @var \Magento\Framework\View\Asset\MergeStrategy\Checksum
     */
    private $checksum;

    protected function setUp()
    {
        $this->mergerMock = $this->getMockForAbstractClass(\Magento\Framework\View\Asset\MergeStrategyInterface::class);
        $this->sourceDir = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->targetDir = $this->getMockForAbstractClass(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class
        );
        $filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->will($this->returnValue($this->sourceDir));
        $filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue($this->targetDir));
        $this->checksum = new Checksum($this->mergerMock, $filesystem);
        $this->assetSource = $this->getMockBuilder(Source::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reflection = new \ReflectionClass(Checksum::class);
        $reflectionProperty = $reflection->getProperty('assetSource');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->checksum, $this->assetSource);

        $this->resultAsset = $this->getMock(\Magento\Framework\View\Asset\File::class, [], [], '', false);
    }

    public function testMergeNoAssets()
    {
        $this->mergerMock->expects($this->never())->method('merge');
        $this->checksum->merge([], $this->resultAsset);
    }

    public function testMergeNoDatFile()
    {
        $this->targetDir->expects($this->once())
            ->method('isExist')
            ->with('merged/result.txt.dat')
            ->will($this->returnValue(false));
        $assets = $this->getAssetsToMerge();
        $this->mergerMock->expects($this->once())->method('merge')->with($assets, $this->resultAsset);
        $this->targetDir->expects($this->once())->method('writeFile')->with('merged/result.txt.dat', '11');
        $this->checksum->merge($assets, $this->resultAsset);
    }

    public function testMergeMtimeChanged()
    {
        $this->targetDir->expects($this->once())
            ->method('isExist')
            ->with('merged/result.txt.dat')
            ->will($this->returnValue(true));
        $this->targetDir->expects($this->once())
            ->method('readFile')
            ->with('merged/result.txt.dat')
            ->will($this->returnValue('10'));
        $assets = $this->getAssetsToMerge();
        $this->mergerMock->expects($this->once())->method('merge')->with($assets, $this->resultAsset);
        $this->targetDir->expects($this->once())->method('writeFile')->with('merged/result.txt.dat', '11');
        $this->checksum->merge($assets, $this->resultAsset);
    }

    public function testMergeMtimeUnchanged()
    {
        $this->targetDir->expects($this->once())
            ->method('isExist')
            ->with('merged/result.txt.dat')
            ->will($this->returnValue(true));
        $this->targetDir->expects($this->once())
            ->method('readFile')
            ->with('merged/result.txt.dat')
            ->will($this->returnValue('11'));
        $assets = $this->getAssetsToMerge();
        $this->mergerMock->expects($this->never())->method('merge');
        $this->targetDir->expects($this->never())->method('writeFile');
        $this->checksum->merge($assets, $this->resultAsset);
    }

    /**
     * Create mocks of assets to merge, as well as a few related necessary mocks
     *
     * @return array
     */
    private function getAssetsToMerge()
    {
        $one = $this->getMock(\Magento\Framework\View\Asset\File::class, [], [], '', false);
        $two = $this->getMock(\Magento\Framework\View\Asset\File::class, [], [], '', false);
        $one->expects($this->never())
            ->method('getSourceFile');
        $two->expects($this->never())
            ->method('getSourceFile');

        $this->assetSource->expects($this->exactly(2))
            ->method('findSource')
            ->withConsecutive([$one], [$two])
            ->willReturnOnConsecutiveCalls('/dir/file/one.txt', '/dir/file/two.txt');

        $this->sourceDir->expects($this->exactly(2))
            ->method('getRelativePath')
            ->will($this->onConsecutiveCalls('file/one.txt', 'file/two.txt'));
        $this->sourceDir->expects($this->exactly(2))->method('stat')->will($this->returnValue(['mtime' => '1']));
        $this->resultAsset->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('merged/result.txt'));
        return [$one, $two];
    }
}
