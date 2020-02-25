<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Observer;

use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\CatalogUrlRewrite\Observer\ProductToWebsiteChangeObserver;
use Magento\Store\Api\StoreWebsiteRelationInterface;

/**
 * Class ProductToWebsiteChangeObserverTest
 *
 * Tests the ProductToWebsiteChangeObserver to ensure the
 * replace method (refresh existing URLs) and deleteByData (remove
 * old URLs) are called the correct number of times.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductToWebsiteChangeObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlPersist;

    /**
     * @var Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var ProductUrlRewriteGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ProductToWebsiteChangeObserver
     */
    protected $model;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $website1;

    /**
     * @var Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $website2;

    /**
     * @var StoreWebsiteRelationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeWebsiteRelation;

    /**
     * Set up
     * Website_ID = 1 -> Store_ID = 1
     * Website_ID = 2 -> Store_ID = 2 & 5
     */
    protected function setUp()
    {
        $this->urlPersist = $this->createMock(UrlPersistInterface::class);
        $this->productUrlRewriteGenerator = $this->createPartialMock(
            ProductUrlRewriteGenerator::class,
            ['generate']
        );

        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->website1 = $this->createPartialMock(Website::class, ['getWebsiteId']);
        $this->website1->expects($this->any())->method('getWebsiteId')->willReturn(1);
        $this->website2 = $this->createPartialMock(Website::class, ['getWebsiteId']);
        $this->website2->expects($this->any())->method('getWebsiteId')->willReturn(2);
        $this->storeManager->expects($this->any())
            ->method('getWebsites')
            ->will($this->returnValue([$this->website1, $this->website2]));

        $this->storeWebsiteRelation = $this->createPartialMock(
            StoreWebsiteRelationInterface::class,
            ['getStoreByWebsiteId']
        );
        $this->storeWebsiteRelation->expects($this->any())
            ->method('getStoreByWebsiteId')
            ->will($this->returnValueMap([[1, [1]], [2, [2, 5]]]));

        $this->product = $this->createPartialMock(
            Product::class,
            ['getId', 'getVisibility', 'getStoreId', 'getWebsiteIds']
        );
        $this->product->expects($this->any())->method('getId')->will($this->returnValue(3));
        $this->productRepository =  $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->productRepository->expects($this->any())->method('getById')->willReturn($this->product);
        $this->event = $this->createPartialMock(
            Event::class,
            ['getProducts', 'getActionType', 'getWebsiteIds']
        );
        $this->event->expects($this->any())->method('getProducts')->willReturn([$this->product]);
        $this->observer = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->observer->expects($this->any())->method('getEvent')->willReturn($this->event);

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            ProductToWebsiteChangeObserver::class,
            [
                'productUrlRewriteGenerator' => $this->productUrlRewriteGenerator,
                'urlPersist' => $this->urlPersist,
                'productRepository' => $this->productRepository,
                'storeWebsiteRelation' => $this->storeWebsiteRelation
            ]
        );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function urlKeyDataProvider()
    {
        return [
            'in_all_no_changes' => [
                'visibilityResult'      => Visibility::VISIBILITY_BOTH,
                'expectedReplaceCount'  => 1,
                'expectedDeleteCount'   => 0,
                'productInWebsites'     => [1, 2],
                'actionType'            => null,
                'websitesChanged'       => [],
                'rewrites'              => [1 => 'url', 2 => 'url', 5 => 'url']
            ],
            'add_to_website_from_empty' => [
                'visibilityResult'      => Visibility::VISIBILITY_BOTH,
                'expectedReplaceCount'  => 1,
                'expectedDeleteCount'   => 0,
                'productInWebsites'     => [1, 2],
                'actionType'            => 'add',
                'websitesChanged'       => [1, 2],
                'rewrites'              => [1 => 'url', 2 => 'url', 5 => 'url']
            ],
            'add_to_website_existing'   => [
                'visibilityResult'      => Visibility::VISIBILITY_BOTH,
                'expectedReplaceCount'  => 1,
                'expectedDeleteCount'   => 0,
                'productInWebsites'     => [1, 2],
                'actionType'            => 'add',
                'websitesChanged'       => [2],
                'rewrites'              => [1 => 'url', 2 => 'url', 5 => 'url']
            ],
            'remove_single' => [
                'visibilityResult'      => Visibility::VISIBILITY_BOTH,
                'expectedReplaceCount'  => 1,
                'expectedDeleteCount'   => 2,
                'productInWebsites'     => [1],
                'actionType'            => 'remove',
                'websitesChanged'       => [2],
                'rewrites'              => [1 => 'url']
            ],
            'remove_all' => [
                'visibilityResult'      => Visibility::VISIBILITY_BOTH,
                'expectedReplaceCount'  => 0,
                'expectedDeleteCount'   => 3,
                'productInWebsites'     => [],
                'actionType'            => 'remove',
                'websitesChanged'       => [1, 2],
                'rewrites'              => []
            ],
            'not_visible_add' => [
                'visibilityResult'      => Visibility::VISIBILITY_NOT_VISIBLE,
                'expectedReplaceCount'  => 0,
                'expectedDeleteCount'   => 0,
                'productInWebsites'     => [1, 2],
                'actionType'            => 'add',
                'websitesChanged'       => [1, 2],
                'rewrites'              => []
            ],
            'not_visible_remove' => [
                'visibilityResult'      => Visibility::VISIBILITY_NOT_VISIBLE,
                'expectedReplaceCount'  => 0,
                'expectedDeleteCount'   => 3,
                'productInWebsites'     => [],
                'actionType'            => 'remove',
                'websitesChanged'       => [1, 2],
                'rewrites'              => []
            ],
        ];
    }

    /**
     * @param bool $visibilityResult
     * @param int $expectedReplaceCount
     * @param int $expectedDeleteCount
     * @param int $productInWebsites
     * @param string $actionType
     * @param array $websitesChanged
     * @param array $rewrites
     *
     * @dataProvider urlKeyDataProvider
     */
    public function testExecuteUrlKey(
        $visibilityResult,
        $expectedReplaceCount,
        $expectedDeleteCount,
        $productInWebsites,
        $actionType,
        $websitesChanged,
        $rewrites
    ) {
        $this->product->expects($this->any())->method('getStoreId')->will(
            $this->returnValue(Store::DEFAULT_STORE_ID)
        );
        $this->product->expects($this->any())->method('getWebsiteIds')->will(
            $this->returnValue($productInWebsites)
        );
        $this->event->expects($this->any())->method('getActionType')->willReturn($actionType);
        $this->event->expects($this->any())->method('getWebsiteIds')->willReturn($websitesChanged);

        $this->productUrlRewriteGenerator->expects($this->any())
            ->method('generate')
            ->will($this->returnValue($rewrites));

        $this->product->expects($this->any())
            ->method('getVisibility')
            ->will($this->returnValue($visibilityResult));

        $this->urlPersist->expects($this->exactly($expectedReplaceCount))
            ->method('replace')
            ->with($rewrites);

        $this->urlPersist->expects($this->exactly($expectedDeleteCount))
            ->method('deleteByData');

        $this->model->execute($this->observer);
    }
}
