<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\CatalogImportExport\Model\ResourceModel\ProductDataLoader;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use \PHPUnit\Framework\TestCase;

class SkuStorageTest extends TestCase
{
    private const LINK_FIELD = 'custom_id';

    /**
     * @var EntityMetadataInterface|MockObject
     */
    private $metadata;

    /**
     * @var SkuStorage
     */
    private $model;

    /**
     * @var ProductDataLoader|MockObject
     */
    private $productDataLoader;

    public function setUp(): void
    {
        $metadataPool = $this->createMock(MetadataPool::class);
        $this->metadata = $this->createMock(EntityMetadataInterface::class);
        $metadataPool->method('getMetadata')->willReturn($this->metadata);
        $this->metadata->method('getLinkField')->willReturn(self::LINK_FIELD);
        $this->productDataLoader = $this->createMock(ProductDataLoader::class);
        $this->productDataLoader->method('getProductsData')->willReturnCallback(function () {
            foreach ($this->getListProductsInDb() as $item) {
                yield $item;
            }
        });

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->model = $objectManager->create(
            SkuStorage::class,
            [
                'metadataPool' => $metadataPool,
                'productDataLoader' => $this->productDataLoader,
            ]
        );
    }

    /**
     * @return void
     */
    public function testHas(): void
    {
        $this->assertFalse($this->model->has('SKU-12'));
        $this->assertTrue($this->model->has('SKU-1'));
    }

    /**
     * @return void
     */
    public function testGetSetReset(): void
    {
        $this->assertNull($this->model->get('SKU-12'));
        $this->assertEquals(
            [
                'entity_id' => '2',
                'type_id' => 'configurable',
                self::LINK_FIELD => '9',
                'attr_set_id' => '5',
            ],
            $this->model->get('SKU-4')
        );

        $this->model->set([
            'sku' => 'SKU-12',
            'entity_id' => 8,
            'type_id' => 'bundle',
            'attribute_set_id' => 1,
            self::LINK_FIELD => 999
        ]);

        $this->assertEquals([
            'entity_id' => '8',
            'type_id' => 'bundle',
            self::LINK_FIELD => '999',
            'attr_set_id' => '1',
        ], $this->model->get('SKU-12'));

        $this->model->reset();
        $this->assertNull($this->model->get('SKU-12'));
    }

    /**
     * @return void
     */
    public function testIterate(): void
    {
        $data = [];
        foreach ($this->model->iterate() as $skuLowered => $item) {
            $data[$skuLowered] = $item;
        }

        $this->assertEquals([
            'sku-1' => [
                'entity_id' => '1',
                'type_id' => 'simple',
                self::LINK_FIELD => '8',
                'attr_set_id' => '3',
            ],
            'sku-4' => [
                'entity_id' => '2',
                'type_id' => 'configurable',
                self::LINK_FIELD => '9',
                'attr_set_id' => '5',
            ],
            'sku-5' => [
                'entity_id' => '3',
                'type_id' => 'configurable',
                self::LINK_FIELD => '11',
                'attr_set_id' => '2',
            ],
        ], $data);
    }

    /**
     * @return array[]
     */
    private function getListProductsInDb(): array
    {
        return [
            [
                'sku' => 'SKU-1',
                'entity_id' => 1,
                'type_id' => 'simple',
                'attribute_set_id' => 3,
                self::LINK_FIELD => 8
            ],
            [
                'sku' => 'SKU-4',
                'entity_id' => 2,
                'type_id' => 'configurable',
                'attribute_set_id' => 5,
                self::LINK_FIELD => 9
            ],

            [
                'sku' => 'SKU-5',
                'entity_id' => 3,
                'type_id' => 'configurable',
                'attribute_set_id' => 2,
                self::LINK_FIELD => 11
            ],
        ];
    }
}
