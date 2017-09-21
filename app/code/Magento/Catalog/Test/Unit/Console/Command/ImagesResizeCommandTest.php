<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Console\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Console\Command\ImagesResizeCommand;
use Magento\Catalog\Model\Product\Image\Cache as ImageCache;
use Magento\Catalog\Model\Product\Image\CacheFactory as ImageCacheFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Tester\CommandTester;
use \Magento\Framework\App\Area;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ImagesResizeCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ImagesResizeCommand
     */
    protected $command;

    /**
     * @var AppState | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $appState;

    /**
     * @var ProductCollectionFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productCollectionFactory;

    /**
     * @var ProductCollection | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productCollection;

    /**
     * @var ProductRepositoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var ImageCacheFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageCacheFactory;

    /**
     * @var ImageCache | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageCache;

    protected function setUp()
    {
        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->prepareProductCollection();
        $this->prepareImageCache();

        $this->command = new ImagesResizeCommand(
            $this->appState,
            $this->productCollectionFactory,
            $this->productRepository,
            $this->imageCacheFactory
        );
    }

    public function testExecuteNoProducts()
    {
        $this->appState->expects($this->once())
            ->method('setAreaCode')
            ->with(Area::AREA_GLOBAL)
            ->willReturnSelf();

        $this->productCollection->expects($this->once())
            ->method('getAllIds')
            ->willReturn([]);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertContains(
            'No product images to resize',
            $commandTester->getDisplay()
        );
    }

    public function testExecute()
    {
        $productsIds = [1, 2];

        $this->appState->expects($this->once())
            ->method('setAreaCode')
            ->with(Area::AREA_GLOBAL)
            ->willReturnSelf();

        $this->productCollection->expects($this->once())
            ->method('getAllIds')
            ->willReturn($productsIds);

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productRepository->expects($this->at(0))
            ->method('getById')
            ->with($productsIds[0])
            ->willReturn($productMock);
        $this->productRepository->expects($this->at(1))
            ->method('getById')
            ->with($productsIds[1])
            ->willThrowException(new NoSuchEntityException());

        $this->imageCache->expects($this->exactly(count($productsIds) - 1))
            ->method('generate')
            ->with($productMock)
            ->willReturnSelf();

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertContains(
            'Product images resized successfully',
            $commandTester->getDisplay()
        );
    }

    public function testExecuteWithException()
    {
        $productsIds = [1];
        $exceptionMessage = 'Test exception text';

        $this->appState->expects($this->once())
            ->method('setAreaCode')
            ->with(Area::AREA_GLOBAL)
            ->willReturnSelf();

        $this->productCollection->expects($this->once())
            ->method('getAllIds')
            ->willReturn($productsIds);

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productRepository->expects($this->exactly(count($productsIds)))
            ->method('getById')
            ->with($productsIds[0])
            ->willReturn($productMock);

        $this->imageCache->expects($this->once())
            ->method('generate')
            ->with($productMock)
            ->willThrowException(new \Exception($exceptionMessage));

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $this->assertContains(
            $exceptionMessage,
            $commandTester->getDisplay()
        );
    }

    protected function prepareProductCollection()
    {
        $this->productCollectionFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->productCollection = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->productCollection);
    }

    protected function prepareImageCache()
    {
        $this->imageCacheFactory = $this->getMockBuilder(\Magento\Catalog\Model\Product\Image\CacheFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->imageCache = $this->getMockBuilder(\Magento\Catalog\Model\Product\Image\Cache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageCacheFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->imageCache);
    }
}
