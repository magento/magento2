<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Search\RequestGenerator;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Search\RequestGenerator\General;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GeneralTest extends TestCase
{
    /** @var  General */
    private $general;

    /** @var  Attribute|MockObject */
    private $attribute;

    protected function setUp(): void
    {
        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMockForAbstractClass();
        $objectManager = new ObjectManager($this);
        $this->general = $objectManager->getObject(General::class);
    }

    public function testGetFilterData()
    {
        $filterName = 'test_general_filter_name';
        $attributeCode = 'test_general_attribute_code';
        $expected = [
            'type' => FilterInterface::TYPE_TERM,
            'name' => $filterName,
            'field' => $attributeCode,
            'value' => '$' . $attributeCode . '$',
        ];
        $this->attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $actual = $this->general->getFilterData($this->attribute, $filterName);
        $this->assertEquals($expected, $actual);
    }

    public function testGetAggregationData()
    {
        $bucketName = 'test_bucket_name';
        $attributeCode = 'test_attribute_code';
        $expected = [
            'type' => BucketInterface::TYPE_TERM,
            'name' => $bucketName,
            'field' => $attributeCode,
            'metric' => [['type' => 'count']],
        ];
        $this->attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $actual = $this->general->getAggregationData($this->attribute, $bucketName);
        $this->assertEquals($expected, $actual);
    }
}
