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
use Magento\Downloadable\Model\Product\TypeHandler\Sample;
use Magento\Downloadable\Model\ResourceModel\Link;
use Magento\Downloadable\Model\SampleFactory;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Downloadable\Model\ResourceModel\SampleFactory as ResourceSampleFactory;
use Magento\Downloadable\Model\ResourceModel\Sample as ResourceSample;

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
     * @var Sample
     */
    private $target;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->sampleFactory = $this->createPartialMock(SampleFactory::class, ['create']);
        $this->sampleResource = $this->createPartialMock(ResourceSample::class, ['deleteItems']);
        $sampleResourceFactory = $this->createPartialMock(ResourceSampleFactory::class, ['create']);
        $sampleResourceFactory->method('create')->willReturn($this->sampleResource);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->metadataMock = $this->createMock(EntityMetadata::class);
        $this->metadataPoolMock->method('getMetadata')->willReturn($this->metadataMock);
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
     * @param \Closure $product
     * @param array $data
     * @param array $modelData
     */
    #[DataProvider('saveDataProvider')]
    public function testSave(\Closure $product, array $data, array $modelData)
    {
        $product = $product($this);
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
    public static function saveDataProvider()
    {
        return [
            [
                'product' => static fn (self $testCase) => $testCase
                    ->createProductMock(100500, 1, 10, [10]),
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
     * @param \Closure $product
     * @param array $data
     * @param array $expectedItems
     */
    #[DataProvider('deleteDataProvider')]
    public function testDelete(\Closure $product, array $data, array $expectedItems)
    {
        $product = $product($this);
        $this->sampleResource->expects($this->once())
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
                'product' =>  static fn (self $testCase) => $testCase
                    ->createProductMock(1, 1, 1, [1]),
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
     * @param Product|MockObject $product
     * @param array $modelData
     * @return \Magento\Downloadable\Model\Sample|MockObject
     */
    private function createSampleModel($product, array $modelData)
    {
        $sample = $this->createPartialMock(
            \Magento\Downloadable\Test\Unit\Helper\SampleTestHelper::class,
            [
                'setProductId',
                'setStoreId',
                'setProductWebsiteIds',
                'setNumberOfDownloads',
                'setLinkFile',
                'setData',
                'setSampleType',
                'setSampleUrl',
                'setSampleFile',
                'save'
            ]
        );
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
     * @return Product|MockObject
     * @throws Exception
     * @internal param bool $isUnlimited
     */
    protected function createProductMock($id, $storeId, $storeWebsiteId, array $websiteIds)
    {
        $product = $this->createPartialMock(
            Product::class,
            ['getId', 'getStoreId', 'getStore', 'getWebsiteIds', 'getData']
        );
        $product->method('getId')->willReturn($id);
        $product->method('getStoreId')->willReturn($storeId);
        $product->method('getWebsiteIds')->willReturn($websiteIds);
        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->method('getWebsiteId')->willReturn($storeWebsiteId);
        $product->method('getStore')->willReturn($store);
        $product->expects($this->any())
            ->method('getData')
            ->with('id')
            ->willReturn($id);
        return $product;
    }
}
