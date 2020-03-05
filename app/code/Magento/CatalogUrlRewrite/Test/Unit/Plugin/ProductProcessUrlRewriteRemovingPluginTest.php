<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Plugin;

use Magento\Catalog\Model\Product\Action;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\Storage\DeleteEntitiesFromStores;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Plugin\ProductProcessUrlRewriteRemovingPlugin;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductProcessUrlRewriteRemovingPluginTest
 *
 * Tests the ProductProcessUrlRewriteRemovingPlugin to ensure the
 * replace method (refresh existing URLs) and deleteByData (remove
 * old URLs) are called the correct number of times.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductProcessUrlRewriteRemovingPluginTest extends TestCase
{
    /**
     * @var UrlPersistInterface|MockObject
     */
    private $urlPersist;

    /**
     * @var Product|MockObject
     */
    private $product1;

    /**
     * @var Product|MockObject
     */
    private $product2;

    /**
     * @var Product|MockObject
     */
    private $product3;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var ProductUrlRewriteGenerator|MockObject
     */
    private $productUrlRewriteGenerator;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductProcessUrlRewriteRemovingPlugin
     */
    private $plugin;

    /**
     * @var Action|MockObject
     */
    private $subject;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Website|MockObject
     */
    private $website1;

    /**
     * @var Website|MockObject
     */
    private $website2;

    /**
     * @var StoreWebsiteRelationInterface|MockObject
     */
    private $storeWebsiteRelation;

    /**
     * @var DeleteEntitiesFromStores|MockObject
     */
    private $deleteEntitiesFromStores;

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

        $this->product1 = $this->createPartialMock(
            Product::class,
            ['getWebsiteIds', 'isVisibleInSiteVisibility']
        );
        $this->product2 = $this->createPartialMock(
            Product::class,
            ['getWebsiteIds', 'isVisibleInSiteVisibility']
        );
        $this->product3 = $this->createPartialMock(
            Product::class,
            ['getWebsiteIds', 'isVisibleInSiteVisibility']
        );

        $this->productRepository =  $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->productRepository->expects($this->any())
            ->method('getById')
            ->will($this->returnValueMap([
                [1, false, 0, true, $this->product1],
                [2, false, 0, true, $this->product2],
                [3, false, 0, true, $this->product3]
            ]));

        $this->deleteEntitiesFromStores = $this->createPartialMock(
            DeleteEntitiesFromStores::class,
            ['execute']
        );

        $this->subject = $this->createMock(
            Action::class
        );

        $this->objectManager = new ObjectManager($this);
        $this->plugin = $this->objectManager->getObject(
            ProductProcessUrlRewriteRemovingPlugin::class,
            [
                'productRepository' => $this->productRepository,
                'storeWebsiteRelation' => $this->storeWebsiteRelation,
                'urlPersist' => $this->urlPersist,
                'productUrlRewriteGenerator' => $this->productUrlRewriteGenerator,
                'deleteEntitiesFromStores' => $this->deleteEntitiesFromStores

            ]
        );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function afterUpdateWebsitesDataProvider()
    {
        return [
            'add_new_websites_1' => [
                'products'              => [
                    '1' => ['visibilityResult' => true, 'websiteids' => [1]],
                    '2' => ['visibilityResult' => true, 'websiteids' => [1]],
                    '3' => ['visibilityResult' => true, 'websiteids' => [1]],
                ],
                'productids'            => [1,2,3],
                'type'                  => 'add',
                'websiteids'            => [2],
                'expectedReplaceCount'  => 3,
                'expectedStoreRemovals' => [],
                'expectedDeleteCount'   => 0,
                'rewrites'              => [true]
            ],
            'add_new_websites_2' => [
                'products'              => [
                    '1' => ['visibilityResult' => true, 'websiteids' => [1]],
                    '2' => ['visibilityResult' => true, 'websiteids' => [1]],
                    '3' => ['visibilityResult' => true, 'websiteids' => [1]],
                ],
                'productids'            => [1,2,3],
                'type'                  => 'add',
                'websiteids'            => [2],
                'expectedReplaceCount'  => 3,
                'expectedStoreRemovals' => [],
                'expectedDeleteCount'   => 0,
                'rewrites'              => [true]
            ],
            'remove_all'   => [
                'products'              => [
                    '1' => ['visibilityResult' => true, 'websiteids' => [1,2]],
                    '2' => ['visibilityResult' => true, 'websiteids' => [1,2]],
                    '3' => ['visibilityResult' => true, 'websiteids' => [1,2]],
                ],
                'productids'            => [1,2,3],
                'type'                  => 'remove',
                'websiteids'            => [1,2],
                'expectedReplaceCount'  => 0,
                'expectedStoreRemovals' => [1,2,5],
                'expectedDeleteCount'   => 1,
                'rewrites'              => []
            ],
            'remove_single' => [
                'products'              => [
                    '1' => ['visibilityResult' => true, 'websiteids' => [1,2]],
                    '2' => ['visibilityResult' => true, 'websiteids' => [1,2]],
                    '3' => ['visibilityResult' => true, 'websiteids' => [1,2]],
                ],
                'productids'            => [1,2,3],
                'type'                  => 'remove',
                'websiteids'            => [2],
                'expectedReplaceCount'  => 0,
                'expectedStoreRemovals' => [2,5],
                'expectedDeleteCount'   => 1,
                'rewrites'              => []
            ],
            'not_visible_add_1' => [
                'products'              => [
                    '1' => ['visibilityResult' => false, 'websiteids' => [1]],
                    '2' => ['visibilityResult' => false, 'websiteids' => [1]],
                    '3' => ['visibilityResult' => false, 'websiteids' => [1]],
                ],
                'productids'            => [1,2,3],
                'type'                  => 'add',
                'websiteids'            => [2],
                'expectedReplaceCount'  => 0,
                'expectedStoreRemovals' => [],
                'expectedDeleteCount'   => 0,
                'rewrites'              => [true]
            ],
            'not_visible_add_2' => [
                'products'              => [
                    '1' => ['visibilityResult' => false, 'websiteids' => [1]],
                    '2' => ['visibilityResult' => false, 'websiteids' => [1]],
                    '3' => ['visibilityResult' => true, 'websiteids' => [1]],
                ],
                'productids'            => [1,2,3],
                'type'                  => 'add',
                'websiteids'            => [2],
                'expectedReplaceCount'  => 1,
                'expectedStoreRemovals' => [],
                'expectedDeleteCount'   => 0,
                'rewrites'              => [true]
            ],

        ];
    }

    /**
     * @param array $products
     * @param array $productids
     * @param string $type
     * @param array $websiteids
     * @param int $expectedReplaceCount
     * @param array $expectedStoreRemovals
     * @param int $expectedDeleteCount
     * @param array $rewrites
     *
     * @dataProvider afterUpdateWebsitesDataProvider
     */
    public function testAfterUpdateWebsites(
        $products,
        $productids,
        $type,
        $websiteids,
        $expectedReplaceCount,
        $expectedStoreRemovals,
        $expectedDeleteCount,
        $rewrites
    ) {

        $this->productUrlRewriteGenerator->expects($this->any())
            ->method('generate')
            ->will($this->returnValue($rewrites));

        $this->product1->expects($this->any())
            ->method('getWebsiteIds')
            ->will($this->returnValue($products['1']['websiteids']));
        $this->product2->expects($this->any())
            ->method('getWebsiteIds')
            ->will($this->returnValue($products['2']['websiteids']));
        $this->product3->expects($this->any())
            ->method('getWebsiteIds')
            ->will($this->returnValue($products['3']['websiteids']));

        $this->product1->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->will($this->returnValue($products['1']['visibilityResult']));
        $this->product2->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->will($this->returnValue($products['2']['visibilityResult']));
        $this->product3->expects($this->any())
            ->method('isVisibleInSiteVisibility')
            ->will($this->returnValue($products['3']['visibilityResult']));

        $this->urlPersist->expects($this->exactly($expectedReplaceCount))
            ->method('replace')
            ->with($rewrites);

        $this->deleteEntitiesFromStores->expects($this->exactly($expectedDeleteCount))
            ->method('execute')
            ->with(
                $expectedStoreRemovals,
                $productids,
                ProductUrlRewriteGenerator::ENTITY_TYPE
            );

        $this->plugin->afterUpdateWebsites(
            $this->subject,
            null,
            $productids,
            $websiteids,
            $type
        );
    }
}
