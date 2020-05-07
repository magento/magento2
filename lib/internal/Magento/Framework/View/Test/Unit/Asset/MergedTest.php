<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\App\View\Deployment\Version\StorageInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\MergeableInterface;
use Magento\Framework\View\Asset\Merged;
use Magento\Framework\View\Asset\MergeStrategyInterface;
use Magento\Framework\View\Asset\Remote;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MergedTest extends TestCase
{
    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var MergeStrategyInterface|MockObject
     */
    private $mergeStrategy;

    /**
     * @var MergeableInterface|MockObject
     */
    private $assetJsOne;

    /**
     * @var MergeableInterface|MockObject
     */
    private $assetJsTwo;

    /**
     * @var AssetRepository|MockObject
     */
    private $assetRepo;

    /**
     * @var StorageInterface|MockObject
     */
    private $versionStorage;

    protected function setUp(): void
    {
        $this->assetJsOne = $this->getMockForAbstractClass(MergeableInterface::class);
        $this->assetJsOne->expects($this->any())
            ->method('getContentType')
            ->willReturn('js');
        $this->assetJsOne->expects($this->any())
            ->method('getPath')
            ->willReturn('script_one.js');

        $this->assetJsTwo = $this->getMockForAbstractClass(MergeableInterface::class);
        $this->assetJsTwo->expects($this->any())
            ->method('getContentType')
            ->willReturn('js');
        $this->assetJsTwo->expects($this->any())
            ->method('getPath')
            ->willReturn('script_two.js');

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->mergeStrategy = $this->getMockForAbstractClass(MergeStrategyInterface::class);
        $this->assetRepo = $this->getMockBuilder(AssetRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->versionStorage = $this->getMockForAbstractClass(StorageInterface::class);
    }

    public function testConstructorNothingToMerge()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('At least one asset has to be passed for merging.');
        new Merged(
            $this->logger,
            $this->mergeStrategy,
            $this->assetRepo,
            [],
            $this->versionStorage
        );
    }

    public function testConstructorRequireMergeInterface()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Asset has to implement \Magento\Framework\View\Asset\MergeableInterface.');
        $assetUrl = new Remote('http://example.com/style.css', 'css');

        (new ObjectManager($this))->getObject(Merged::class, [
            'logger' => $this->logger,
            'mergeStrategy' => $this->mergeStrategy,
            'assetRepo' => $this->assetRepo,
            'assets' => [$this->assetJsOne, $assetUrl],
            'versionStorage' => $this->versionStorage,
        ]);
    }

    public function testConstructorIncompatibleContentTypes()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Content type \'css\' cannot be merged with \'js\'.');
        $assetCss = $this->getMockForAbstractClass(MergeableInterface::class);
        $assetCss->expects($this->any())
            ->method('getContentType')
            ->willReturn('css');

        (new ObjectManager($this))->getObject(Merged::class, [
            'logger' => $this->logger,
            'mergeStrategy' => $this->mergeStrategy,
            'assetRepo' => $this->assetRepo,
            'assets' => [$this->assetJsOne, $assetCss],
            'versionStorage' => $this->versionStorage,
        ]);
    }

    public function testIteratorInterfaceMerge()
    {
        $assets = [$this->assetJsOne, $this->assetJsTwo];

        $this->logger->expects($this->never())->method('critical');

        /** @var Merged $merged */
        $merged = (new ObjectManager($this))->getObject(Merged::class, [
            'logger' => $this->logger,
            'mergeStrategy' => $this->mergeStrategy,
            'assetRepo' => $this->assetRepo,
            'assets' => $assets,
            'versionStorage' => $this->versionStorage,
        ]);

        $mergedAsset = $this->createMock(File::class);
        $this->mergeStrategy
            ->expects($this->once())
            ->method('merge')
            ->with($assets, $mergedAsset)
            ->willReturn(null);
        $this->assetRepo->expects($this->once())
            ->method('createArbitrary')
            ->willReturn($mergedAsset);
        $expectedResult = [$mergedAsset];

        $this->assertIteratorEquals($expectedResult, $merged);
        $this->assertIteratorEquals($expectedResult, $merged); // ensure merging happens only once
    }

    public function testIteratorInterfaceMergeFailure()
    {
        $mergeError = new \Exception('File not found');
        $assetBroken = $this->getMockForAbstractClass(MergeableInterface::class);
        $assetBroken->expects($this->any())
            ->method('getContentType')
            ->willReturn('js');
        $assetBroken->expects($this->any())
            ->method('getPath')
            ->willThrowException($mergeError);

        /** @var Merged $merged */
        $merged = (new ObjectManager($this))->getObject(Merged::class, [
            'logger' => $this->logger,
            'mergeStrategy' => $this->mergeStrategy,
            'assetRepo' => $this->assetRepo,
            'assets' => [$this->assetJsOne, $this->assetJsTwo, $assetBroken],
            'versionStorage' => $this->versionStorage,
        ]);

        $this->logger->expects($this->once())->method('critical')->with($this->identicalTo($mergeError));

        $expectedResult = [$this->assetJsOne, $this->assetJsTwo, $assetBroken];
        $this->assertIteratorEquals($expectedResult, $merged);
        $this->assertIteratorEquals($expectedResult, $merged); // ensure merging attempt happens only once
    }

    /**
     * Assert that iterator items equal to expected ones
     *
     * @param array $expectedItems
     * @param \Iterator $actual
     */
    protected function assertIteratorEquals(array $expectedItems, \Iterator $actual)
    {
        $actualItems = [];
        foreach ($actual as $actualItem) {
            $actualItems[] = $actualItem;
        }
        $this->assertEquals($expectedItems, $actualItems);
    }
}
