<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Product\TypeHandler;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Helper\Download;
use Magento\Downloadable\Model\Product\TypeHandler\Sample;
use Magento\Downloadable\Model\ResourceModel\Link;
use Magento\Downloadable\Model\SampleFactory;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Downloadable\Model\Product\TypeHandler\Sample
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SampleTest extends TestCase
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
     * @var Link|MockObject
     */
    private $sampleResource;

    /**
     * @var SampleFactory|MockObject
     */
    private $sampleFactory;

    /**
     * @var \Magento\Downloadable\Model\Product\TypeHandler\Sample
     */
    private $target;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->sampleFactory = $this->getMockBuilder(SampleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->sampleResource = $this->getMockBuilder(\Magento\Downloadable\Model\ResourceModel\Sample::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteItems'])
            ->getMock();
        $sampleResourceFactory = $this->getMockBuilder(\Magento\Downloadable\Model\ResourceModel\SampleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $sampleResourceFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->sampleResource);
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock = $this->createMock(EntityMetadata::class);
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')->willReturn($this->metadataMock);
        $this->target = $objectManagerHelper->getObject(
            Sample::class,
            [
                'sampleFactory' => $this->sampleFactory,
                'sampleResourceFactory' => $sampleResourceFactory,
                'metadataPool' => $this->metadataPoolMock
            ]
        );
        $refClass = new \ReflectionClass(Sample::class);
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
        $link = $this->createSampleModel($product, $modelData);
        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn('id');
        $this->sampleFactory->expects($this->once())
            ->method('create')
            ->willReturn($link);
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
                    'sample' => [
                        [
                            'is_delete' => 0,
                            'sample_id' => 0,
                            'title' => 'Downloadable Product Sample Title',
                            'type' => Download::LINK_TYPE_FILE,
                            'sample_url' => null,
                            'sort_order' => '0',
                        ],
                    ],
                ],
                'modelData' => [
                    'title' => 'Downloadable Product Sample Title',
                    'type' => Download::LINK_TYPE_FILE,
                    'sample_url' => null,
                    'sort_order' => '0',
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
        $this->sampleResource->expects($this->once())
            ->method('deleteItems')
            ->with($expectedItems);
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
                    'sample' => [
                        [
                            'sample_id' => 1,
                            'is_delete' => 1,
                        ],
                        [
                            'sample_id' => 2,
                            'is_delete' => 1,
                        ],
                        [
                            'sample_id' => null,
                            'is_delete' => 1,
                        ],
                        [
                            'sample_id' => false,
                            'is_delete' => 1,
                        ],
                        [
                            'sample_id' => 456,
                            'is_delete' => 1,
                        ],
                    ]
                ],
                'expectedItems' => [1, 2, 456]
            ]
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product|MockObject $product
     * @param array $modelData
     * @return \Magento\Downloadable\Model\Sample|MockObject
     */
    private function createSampleModel($product, array $modelData)
    {
        $sample = $this->getMockBuilder(\Magento\Downloadable\Model\Sample::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setData',
                    'setSampleType',
                    'setProductId',
                    'setStoreId',
                    'setProductWebsiteIds',
                    'setNumberOfDownloads',
                    'setSampleUrl',
                    'setLinkFile',
                    'setSampleFile',
                    'save',
                ]
            )
            ->getMock();
        $sample->expects($this->once())
            ->method('setData')
            ->with($modelData)->willReturnSelf();
        $sample->expects($this->once())
            ->method('setSampleType')
            ->with($modelData['type'])->willReturnSelf();
        $sample->expects($this->once())
            ->method('setProductId')
            ->with($product->getData('id'))
            ->willReturnSelf();
        $sample->expects($this->once())
            ->method('setStoreId')
            ->with($product->getStoreId())->willReturnSelf();

        return $sample;
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
            ->setMethods(['getId', 'getStoreId', 'getStore', 'getWebsiteIds', 'getData'])
            ->getMock();
        $product->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $product->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);
        $product->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn($websiteIds);
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
            ->getMock();
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($storeWebsiteId);
        $product->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $product->expects($this->any())
            ->method('getData')
            ->with('id')
            ->willReturn($id);
        return $product;
    }
}
