<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogUrlRewrite\Observer\ProductToWebsiteChangeObserver;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Store\Model\Store;

/**
 * Test for ProductToWebsiteChangeObserver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductToWebsiteChangeObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var ProductUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlPersist;

    /**
     * @var Event|\PHPUnit_Framework_MockObject_MockObject
     */
    private $event;

    /**
     * @var Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $observer;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductToWebsiteChangeObserver
     */
    private $model;

    /**
     * @var int
     */
    private $productId;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->productId = 3;

        $this->urlPersist = $this->getMockBuilder(UrlPersistInterface::class)
            ->setMethods(['deleteByData', 'replace'])
            ->getMockForAbstractClass();
        $this->productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->setMethods(['getById'])
            ->getMockForAbstractClass();
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getVisibility'])
            ->getMock();
        $this->product->expects($this->any())
            ->method('getId')
            ->willReturn($this->productId);
        $this->productRepository->expects($this->any())
            ->method('getById')
            ->with($this->productId, false, Store::DEFAULT_STORE_ID)
            ->willReturn($this->product);
        $this->productUrlRewriteGenerator = $this->getMockBuilder(ProductUrlRewriteGenerator::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();
        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProducts'])
            ->getMock();
        $this->event->expects($this->any())
            ->method('getProducts')
            ->willReturn([$this->productId]);
        $this->observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
        $this->observer->expects($this->any())
            ->method('getEvent')
            ->willReturn($this->event);
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->request->expects($this->any())
            ->method('getParam')
            ->with('store_id', Store::DEFAULT_STORE_ID)
            ->willReturn(Store::DEFAULT_STORE_ID);

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            ProductToWebsiteChangeObserver::class,
            [
                'productUrlRewriteGenerator' => $this->productUrlRewriteGenerator,
                'urlPersist' => $this->urlPersist,
                'productRepository' => $this->productRepository,
                'request' => $this->request
            ]
        );
    }

    /**
     * @param array $urlRewriteGeneratorResult
     * @param int $numberDeleteByData
     * @param int $productVisibility
     * @param int $numberReplace
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        array $urlRewriteGeneratorResult,
        int $numberDeleteByData,
        int $productVisibility,
        int $numberReplace
    ) {
        $this->productUrlRewriteGenerator->expects($this->any())
            ->method('generate')
            ->willReturn($urlRewriteGeneratorResult);
        $this->urlPersist->expects($this->exactly($numberDeleteByData))
            ->method('deleteByData')
            ->with(
                [
                    UrlRewrite::ENTITY_ID => $this->productId,
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => Store::DEFAULT_STORE_ID
                ]
            );
        $this->product->expects($this->any())
            ->method('getVisibility')
            ->willReturn($productVisibility);
        $this->urlPersist->expects($this->exactly($numberReplace))
            ->method('replace')
            ->with($urlRewriteGeneratorResult);

        $this->model->execute($this->observer);
    }

    /**
     * Data provider for testExecute
     *
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            [[], 0, Visibility::VISIBILITY_NOT_VISIBLE, 0],
            [['someRewrite'], 1, Visibility::VISIBILITY_NOT_VISIBLE, 0],
            [['someRewrite'], 1, Visibility::VISIBILITY_BOTH, 1],
        ];
    }
}
