<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\DataProvider\Product\LayeredNavigation\Builder;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\AttributeOptionProvider;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Attribute;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Formatter\LayerFormatter;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\AggregationValueInterface;
use Magento\Framework\Api\Search\BucketInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Attribute builder
 */
class AttributeTest extends TestCase
{
    /**
     * @var AttributeOptionProvider|MockObject
     */
    private $attributeOptionProvider;

    /**
     * @var LayerFormatter|MockObject
     */
    private $layerFormatter;

    /**
     * @var Yesno|MockObject
     */
    private $yesNo;

    /**
     * @var Attribute
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->attributeOptionProvider = $this->createMock(AttributeOptionProvider::class);
        $this->layerFormatter = $this->createMock(LayerFormatter::class);
        $this->yesNo = $this->createMock(Yesno::class);
        $this->model = new Attribute(
            $this->attributeOptionProvider,
            $this->layerFormatter,
            $this->yesNo
        );
    }

    /**
     * Test build() method when attribute is empty
     */
    public function testBuildWithEmptyAttribute(): void
    {
        $storeId = 1;
        $bucketName = 'color_bucket';
        $attributeCode = 'color';
        $aggregation = $this->getAggregation($bucketName);
        $this->attributeOptionProvider->expects($this->once())
            ->method('getOptions')
            ->with(['1'], $storeId, [$attributeCode])
            ->willReturn([]);
        $this->layerFormatter->expects($this->once())
            ->method('buildLayer')
            ->with($bucketName, 0, $bucketName, null)
            ->willReturn([
                'label' => $bucketName,
                'count' => 0,
                'attribute_code' => $bucketName,
                'position' => null
            ]);
        $this->layerFormatter->expects($this->once())
            ->method('buildItem')
            ->with('1', '1', 5)
            ->willReturn([
                'label' => '1',
                'value' => '1',
                'count' => 5
            ]);
        $result = $this->model->build($aggregation, $storeId);
        $this->assertIsArray($result);
        $this->assertArrayHasKey($bucketName, $result);
        $this->assertEquals(1, $result[$bucketName]['count']);
        $this->assertArrayHasKey('options', $result[$bucketName]);
        $this->assertCount(1, $result[$bucketName]['options']);
    }

    /**
     * Test build() method when attribute is not empty
     */
    public function testBuildWithNonEmptyAttribute(): void
    {
        $storeId = 1;
        $bucketName = 'color_bucket';
        $attributeCode = 'color';
        $attributeLabel = 'Color';
        $attributePosition = 10;
        $aggregation = $this->getAggregation($bucketName);
        $attributeData = [
            $attributeCode => [
                'attribute_code' => $attributeCode,
                'attribute_label' => $attributeLabel,
                'attribute_type' => 'select',
                'position' => $attributePosition,
                'is_filterable' => 0,
                'options' => [
                    '1' => 'Red',
                    '2' => 'Blue'
                ]
            ]
        ];
        $this->attributeOptionProvider->expects($this->once())
            ->method('getOptions')
            ->with(['1'], $storeId, [$attributeCode])
            ->willReturn($attributeData);
        $this->layerFormatter->expects($this->once())
            ->method('buildLayer')
            ->with($attributeLabel, 0, $attributeCode, $attributePosition)
            ->willReturn([
                'label' => $attributeLabel,
                'count' => 0,
                'attribute_code' => $attributeCode,
                'position' => $attributePosition
            ]);
        $this->layerFormatter->expects($this->exactly(2))
            ->method('buildItem')
            ->willReturnCallback(function ($label, $value, $count) {
                return [
                    'label' => $label,
                    'value' => $value,
                    'count' => $count
                ];
            });
        $result = $this->model->build($aggregation, $storeId);
        $this->assertIsArray($result);
        $this->assertArrayHasKey($bucketName, $result);
        $this->assertEquals($attributeLabel, $result[$bucketName]['label']);
        $this->assertEquals($attributeCode, $result[$bucketName]['attribute_code']);
        $this->assertEquals($attributePosition, $result[$bucketName]['position']);
        $this->assertArrayHasKey('options', $result[$bucketName]);
    }

    /**
     * @param string $bucketName
     *
     * @return AggregationInterface|MockObject
     */
    private function getAggregation(string $bucketName): MockObject|AggregationInterface
    {
        $aggregation = $this->createMock(AggregationInterface::class);
        $bucket = $this->createMock(BucketInterface::class);
        $aggregationValue = $this->createMock(AggregationValueInterface::class);
        $bucket->expects($this->atLeastOnce())->method('getName')->willReturn($bucketName);
        $bucket->expects($this->atLeastOnce())->method('getValues')->willReturn([$aggregationValue]);
        $aggregationValue->expects($this->atLeastOnce())->method('getValue')->willReturn('1');
        $aggregationValue->expects($this->atLeastOnce())
            ->method('getMetrics')
            ->willReturn(['value' => '1', 'count' => 5]);
        $aggregation->expects($this->atLeastOnce())->method('getBuckets')->willReturn([$bucket]);
        return $aggregation;
    }
}
