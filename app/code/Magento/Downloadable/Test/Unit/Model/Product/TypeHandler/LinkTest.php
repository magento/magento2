<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Product\TypeHandler;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Model\Link as LinkModel;
use Magento\Downloadable\Model\LinkFactory;
use Magento\Downloadable\Model\Product\TypeHandler\Link;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Downloadable\Model\ResourceModel\Link as LinkResource;

/**
 * Test for \Magento\Downloadable\Model\Product\TypeHandler\Link
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var MockObject
     */
    protected $metadataMock;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link|MockObject
     */
    private $linkResource;

    /**
     * @var LinkFactory|MockObject
     */
    private $linkFactory;

    /**
     * @var Link
     */
    private $target;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->linkFactory = $this->createPartialMock(LinkFactory::class, ['create']);
        $this->linkResource = $this->createPartialMock(LinkResource::class, ['deleteItems']);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->metadataMock = $this->createMock(EntityMetadata::class);
        $this->metadataMock->method('getLinkField')->willReturn('id');
        $this->metadataPoolMock->method('getMetadata')->willReturn($this->metadataMock);
        $this->target = $objectManagerHelper->getObject(
            Link::class,
            [
                'linkFactory' => $this->linkFactory,
                'linkResource' => $this->linkResource
            ]
        );
        $refClass = new \ReflectionClass(Link::class);
        $refProperty = $refClass->getProperty('metadataPool');
        $refProperty->setValue($this->target, $this->metadataPoolMock);
    }

    /**
     * @param \Closure $product
     * @param array $data
     * @param array $modelData
     */
    #[DataProvider('saveDataProvider')]
    public function testSave(\Closure $product, array $data, array $modelData)
    {
        $product = $product($this);
        $link = $this->createLinkkModel($product, $modelData, true);
        $this->linkFactory->expects($this->once())
            ->method('create')
            ->willReturn($link);
        $product->expects($this->once())
            ->method('setIsCustomOptionChanged')->willReturnSelf();
        $this->target->save($product, $data);
    }

    /**
     * @return array
     */
    public static function saveDataProvider()
    {
        return [
            [
                'product' => static fn (self $testCase) => $testCase
                    ->createProductMock(100500, 1, 10, [10]),
                'data' => [
                    'link' => [
                        [
                            'link_id' => 0,
                            'product_id' => 1,
                            'sort_order' => '0',
                            'title' => 'Downloadable Product Link',
                            'sample' => [
                                'type' => Download::LINK_TYPE_FILE,
                                'url' => null,
                                'file' => json_encode(
                                    [
                                        [
                                            'file' => '/n/d/jellyfish_1_3.jpg',
                                            'name' => 'jellyfish_1_3.jpg',
                                            'size' => 54565,
                                            'status' => 0,
                                        ],
                                    ]
                                ),
                            ],
                            'type' => Download::LINK_TYPE_FILE,
                            'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
                            'link_url' => null,
                            'is_delete' => 0,
                            'number_of_downloads' => 15,
                            'price' => 15.00,
                        ],
                    ],
                    'sample' => [
                        [
                            'is_delete' => 0,
                            'sample_id' => 0,
                            'title' => 'Downloadable Product Sample Title',
                            'type' => Download::LINK_TYPE_FILE,
                            'file' => json_encode(
                                [
                                    [
                                        'file' => '/f/u/jellyfish_1_4.jpg',
                                        'name' => 'jellyfish_1_4.jpg',
                                        'size' => 1024,
                                        'status' => 0,
                                    ],
                                ]
                            ),
                            'sample_url' => null,
                            'sort_order' => '0',
                        ],
                    ],
                ],
                'modelData' => [
                    'product_id' => 1,
                    'sort_order' => '0',
                    'title' => 'Downloadable Product Link',
                    'type' => Download::LINK_TYPE_FILE,
                    'is_shareable' => \Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG,
                    'link_url' => null,
                    'number_of_downloads' => 15,
                    'price' => 15.00,
                ]
            ]
        ];
    }

    /**
     * @param \Closure $product
     * @param array $data
     * @param array $expectedItems
     */
    #[DataProvider('deleteDataProvider')]
    public function testDelete(\Closure $product, array $data, array $expectedItems)
    {
        $product = $product($this);
        $this->linkResource->expects($this->once())
            ->method('deleteItems')
            ->with($expectedItems);
        $this->target->save($product, $data);
    }

    /**
     * @return array
     */
    public static function deleteDataProvider()
    {
        return [
            [
                'product' => static fn (self $testCase) => $testCase
                    ->createProductMock(1, 1, 1, [1]),
                'data' => [
                    'link' => [
                        [
                            'link_id' => 1,
                            'is_delete' => 1,
                        ],
                        [
                            'link_id' => 2,
                            'is_delete' => 1,
                        ],
                        [
                            'link_id' => null,
                            'is_delete' => 1,
                        ],
                        [
                            'link_id' => false,
                            'is_delete' => 1,
                        ],
                        [
                            'link_id' => 890,
                            'is_delete' => 1,
                        ],
                    ]
                ],
                'expectedItems' => [1, 2, 890]
            ]
        ];
    }

    /**
     * @param Product|MockObject $product
     * @param array $modelData
     * @param bool $isUnlimited
     * @return \Magento\Downloadable\Model\Link|MockObject
     */
    private function createLinkkModel($product, array $modelData, $isUnlimited)
    {
        $link = $this->createPartialMockWithReflection(
            LinkModel::class,
            [
                'setProductId',
                'setStoreId',
                'setWebsiteId',
                'setProductWebsiteIds',
                'getIsUnlimited',
                'setData',
                'setLinkType',
                'setPrice',
                'setNumberOfDownloads',
                'setSampleUrl',
                'setSampleType',
                'setLinkFile',
                'setSampleFile',
                'save'
            ]
        );
        $link->expects($this->once())
            ->method('setData')
            ->with($modelData)->willReturnSelf();
        $link->expects($this->once())
            ->method('setLinkType')
            ->with($modelData['type'])->willReturnSelf();
        $link->expects($this->once())
            ->method('setProductId')
            ->with($product->getData('id'))->willReturnSelf();
        $link->expects($this->once())
            ->method('setStoreId')
            ->with($product->getStoreId())->willReturnSelf();
        $link->expects($this->once())
            ->method('setWebsiteId')
            ->with($product->getStore()->getWebsiteId())->willReturnSelf();
        $link->expects($this->once())
            ->method('setPrice')
            ->with(0);
        $link->expects($this->any())
            ->method('setNumberOfDownloads')
            ->with(0);
        $link->expects($this->once())
            ->method('getIsUnlimited')
            ->willReturn($isUnlimited);
        return $link;
    }

    /**
     * @param int $id
     * @param int $storeId
     * @param int $storeWebsiteId
     * @param array $websiteIds
     * @return Product|MockObject
     * @internal param bool $isUnlimited
     */
    protected function createProductMock($id, $storeId, $storeWebsiteId, array $websiteIds)
    {
        $product = $this->createPartialMockWithReflection(
            Product::class,
            [
                'getLinksPurchasedSeparately',
                'setIsCustomOptionChanged',
                'getId',
                'getStoreId',
                'getStore',
                'getWebsiteIds',
                'getData'
            ]
        );
        $product->method('getId')->willReturn($id);
        $product->method('getStoreId')->willReturn($storeId);
        $product->method('getWebsiteIds')->willReturn($websiteIds);
        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->method('getWebsiteId')->willReturn($storeWebsiteId);
        $product->method('getStore')->willReturn($store);
        $product->method('getLinksPurchasedSeparately')->willReturn(true);
        $product->expects($this->any())
            ->method('getData')
            ->with('id')
            ->willReturn($id);
        return $product;
    }
}
