<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Console\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Console\Command\ImagesResizeCommand;
use Magento\Catalog\Console\ImageResizeOptions;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\Process\Cache as ImageCache;
use Magento\Catalog\Model\Product\Image\Process\CacheFactory as ImageCacheFactory;
use Magento\Catalog\Model\Product\Image\Process\Queue;
use Magento\Catalog\Model\Product\Image\Process\QueueFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit_Framework_MockObject_MockObject as Mock;
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
     * @var AppState | Mock
     */
    protected $appState;

    /**
     * @var ProductCollectionFactory | Mock
     */
    protected $productCollectionFactory;

    /**
     * @var ProductCollection | Mock
     */
    protected $productCollection;

    /**
     * @var ProductRepositoryInterface | Mock
     */
    protected $productRepository;

    /**
     * @var ImageCacheFactory | Mock
     */
    protected $imageCacheFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection | Mock
     */
    protected $resourceConnection;

    /**
     * @var QueueFactory | Mock
     */
    protected $queueFactory;

    /**
     * @var Queue | Mock
     */
    protected $queue;

    /**
     * @var ImageCache | Mock
     */
    protected $imageCache;

    /**
     * @var \Symfony\Component\Console\Output\Output | Mock
     */
    protected $output;

    protected function setUp()
    {
        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->resourceConnection = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->output = $this->getMockBuilder(\Symfony\Component\Console\Output\Output::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->prepareProductCollection();
        $this->prepareImageCache();
        $this->prepareQueueFactory();

        $this->command = new ImagesResizeCommand(
            $this->appState,
            $this->productCollectionFactory,
            $this->queueFactory,
            new ImageResizeOptions()
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

    /**
     * @dataProvider resizeDataProvider
     * @param int $expectProducts
     * @param null|int $limit
     * @param null|int $offset
     */
    public function testExecute($expectProducts, $limit = null, $offset = null)
    {
        $productsIds = [1, 2, 3, 4];

        $this->appState->expects($this->once())
            ->method('setAreaCode')
            ->with(Area::AREA_GLOBAL)
            ->willReturnSelf();

        $this->productCollection->expects($this->once())
            ->method('getAllIds')
            ->willReturn($productsIds);

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (!$offset) {
            $this->productRepository->expects($this->at(0))
                ->method('getById')
                ->with($productsIds[0])
                ->willReturn($productMock);

            $this->productRepository->expects($this->at(1))
                ->method('getById')
                ->with($productsIds[1])
                ->willThrowException(new NoSuchEntityException());

            if (!$limit) {
                $this->productRepository->expects($this->at(2))
                    ->method('getById')
                    ->with($productsIds[2])
                    ->willReturn($productMock);

                $this->productRepository->expects($this->at(3))
                    ->method('getById')
                    ->with($productsIds[3])
                    ->willReturn($productMock);
            }
        }

        if ($offset && $offset < 4) {
            $this->productRepository->expects($this->at(0))
                ->method('getById')
                ->with($productsIds[2])
                ->willReturn($productMock);

            $this->productRepository->expects($this->at(1))
                ->method('getById')
                ->with($productsIds[3])
                ->willReturn($productMock);
        }

        if ($offset === 4) {
            $this->productRepository->expects($this->never())
                ->method('getById');
        }

        $this->imageCache->expects($this->exactly($expectProducts))
            ->method('generate')
            ->with($productMock)
            ->willReturnSelf();

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([
            '--' . ImageResizeOptions::PRODUCT_LIMIT => $limit,
            '--' . ImageResizeOptions::PRODUCT_OFFSET => $offset,
        ]);

        $message = 'Product images resized successfully';
        if ($offset === 4) {
            $message = 'Offset may not be higher than 3';
        }
        $this->assertContains($message, $commandTester->getDisplay());
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

        $productMock = $this->getMockBuilder(Product::class)
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
            ProductCollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->productCollection = $this->getMockBuilder(
            ProductCollection::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->productCollectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->productCollection);
    }

    protected function prepareImageCache()
    {
        $this->imageCacheFactory = $this->getMockBuilder(ImageCacheFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->imageCache = $this->getMockBuilder(ImageCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->imageCacheFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->imageCache);
    }

    protected function prepareQueueFactory()
    {
        $this->queueFactory = $this->getMockBuilder(QueueFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->queue = $this->getMock(
            Queue::class,
            [],
            [
                'productRepository' => $this->productRepository,
                'resourceConnection' => $this->resourceConnection,
                'imageCacheFactory' => $this->imageCacheFactory,
                'output' => $this->output,
            ],
            '',
            false,
            true,
            true,
            false,
            true
        );

        $this->queueFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->queue);
    }

    /**
     * @return array
     */
    public function resizeDataProvider()
    {
        return [
            [
                'expectProducts' => 3,
                ImageResizeOptions::PRODUCT_LIMIT => null,
                ImageResizeOptions::PRODUCT_OFFSET => null,
            ],
            [
                'expectProducts' => 1,
                ImageResizeOptions::PRODUCT_LIMIT => 2,
                ImageResizeOptions::PRODUCT_OFFSET => null,
            ],
            [
                'expectProducts' => 2,
                ImageResizeOptions::PRODUCT_LIMIT => 2,
                ImageResizeOptions::PRODUCT_OFFSET => 2,
            ],
            [
                'expectProducts' => 0,
                ImageResizeOptions::PRODUCT_LIMIT => 2,
                ImageResizeOptions::PRODUCT_OFFSET => 4,
            ],
        ];
    }
}
