<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Product\TypeHandler;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Model\LinkFactory;
use Magento\Downloadable\Model\Product\TypeHandler\Link;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Downloadable\Model\Product\TypeHandler\Link
 */
class LinkTest extends TestCase
{
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
     * @var \Magento\Downloadable\Model\Product\TypeHandler\Link
     */
    private $target;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->linkFactory = $this->getMockBuilder(LinkFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->linkResource = $this->getMockBuilder(\Magento\Downloadable\Model\ResourceModel\Link::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteItems'])
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock = $this->createMock(EntityMetadata::class);
        $this->metadataMock->expects($this->any())->method('getLinkField')->willReturn('id');
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($this->metadataMock);
        $this->target = $objectManagerHelper->getObject(
            Link::class,
            [
                'linkFactory' => $this->linkFactory,
                'linkResource' => $this->linkResource
            ]
        );
        $refClass = new \ReflectionClass(Link::class);
        $refProperty = $refClass->getProperty('metadataPool');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->target, $this->metadataPoolMock);
    }

    /**
     * @dataProvider saveDataProvider
     * @param \Magento\Catalog\Model\Product|MockObject $product
     * @param array $data
     * @param array $modelData
     */
    public function testSave($product, array $data, array $modelData)
    {
        $link = $this->createLinkkModel($product, $modelData, true);
        $this->linkFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($link));
        $product->expects($this->once())
            ->method('setIsCustomOptionChanged')
            ->will($this->returnSelf());
        $this->target->save($product, $data);
    }

    /**
     * @return array
     */
    public function saveDataProvider()
    {
        return [
            [
                'product' => $this->createProductMock(100500, 1, 10, [10]),
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
     * @param \Magento\Catalog\Model\Product|MockObject $product
     * @param array $data
     * @param array $expectedItems
     * @dataProvider deleteDataProvider
     */
    public function testDelete($product, array $data, array $expectedItems)
    {
        $this->linkResource->expects($this->once())
            ->method('deleteItems')
            ->with($this->equalTo($expectedItems));
        $this->target->save($product, $data);
    }

    /**
     * @return array
     */
    public function deleteDataProvider()
    {
        return [
            [
                'product' => $this->createProductMock(1, 1, 1, [1]),
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
     * @param \Magento\Catalog\Model\Product|MockObject $product
     * @param array $modelData
     * @param bool $isUnlimited
     * @return \Magento\Downloadable\Model\Link|MockObject
     */
    private function createLinkkModel($product, array $modelData, $isUnlimited)
    {
        $link = $this->getMockBuilder(\Magento\Downloadable\Model\Link::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setData',
                    'setLinkType',
                    'setProductId',
                    'setStoreId',
                    'setWebsiteId',
                    'setProductWebsiteIds',
                    'setPrice',
                    'setNumberOfDownloads',
                    'setSampleUrl',
                    'setSampleType',
                    'setLinkFile',
                    'setSampleFile',
                    'save',
                    'getIsUnlimited'
                ]
            )
            ->getMock();
        $link->expects($this->once())
            ->method('setData')
            ->with($modelData)
            ->will($this->returnSelf());
        $link->expects($this->once())
            ->method('setLinkType')
            ->with($modelData['type'])
            ->will($this->returnSelf());
        $link->expects($this->once())
            ->method('setProductId')
            ->with($product->getData('id'))
            ->will($this->returnSelf());
        $link->expects($this->once())
            ->method('setStoreId')
            ->with($product->getStoreId())
            ->will($this->returnSelf());
        $link->expects($this->once())
            ->method('setWebsiteId')
            ->with($product->getStore()->getWebsiteId())
            ->will($this->returnSelf());
        $link->expects($this->once())
            ->method('setPrice')
            ->with(0);
        $link->expects($this->any())
            ->method('setNumberOfDownloads')
            ->with(0);
        $link->expects($this->once())
            ->method('getIsUnlimited')
            ->will($this->returnValue($isUnlimited));
        return $link;
    }

    /**
     * @param int $id
     * @param int $storeId
     * @param int $storeWebsiteId
     * @param array $websiteIds
     * @return \Magento\Catalog\Model\Product|MockObject
     * @internal param bool $isUnlimited
     */
    private function createProductMock($id, $storeId, $storeWebsiteId, array $websiteIds)
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getStoreId',
                    'getStore',
                    'getWebsiteIds',
                    'getLinksPurchasedSeparately',
                    'setIsCustomOptionChanged',
                    'getData'
                ]
            )
            ->getMock();
        $product->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));
        $product->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));
        $product->expects($this->any())
            ->method('getWebsiteIds')
            ->will($this->returnValue($websiteIds));
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
            ->getMock();
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue($storeWebsiteId));
        $product->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));
        $product->expects($this->any())
            ->method('getLinksPurchasedSeparately')
            ->will($this->returnValue(true));
        $product->expects($this->any())
            ->method('getData')
            ->with('id')
            ->willReturn($id);
        return $product;
    }
}
