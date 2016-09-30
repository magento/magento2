<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\RequestGenerator;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Search\RequestGenerator\Decimal;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\FilterInterface;

class DecimalTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Decimal */
    private $decimal;

    /** @var  Attribute|\PHPUnit_Framework_MockObject_MockObject */
    private $attribute;

    protected function setUp()
    {
        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->decimal = $objectManager->getObject(Decimal::class);
    }

    public function testGetFilterData()
    {
        $filterName = 'test_filter_name';
        $attributeCode = 'test_attribute_code';
        $expected = [
            'type' => FilterInterface::TYPE_RANGE,
            'name' => $filterName,
            'field' => $attributeCode,
            'from' => '$' . $attributeCode . '.from$',
            'to' => '$' . $attributeCode . '.to$',
        ];
        $this->attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $actual = $this->decimal->getFilterData($this->attribute, $filterName);
        $this->assertEquals($expected, $actual);
    }

    public function testGetAggregationData()
    {
        $bucketName = 'test_bucket_name';
        $attributeCode = 'test_attribute_code';
        $expected = [
            'type' => BucketInterface::TYPE_DYNAMIC,
            'name' => $bucketName,
            'field' => $attributeCode,
            'method' => 'manual',
            'metric' => [['type' => 'count']],
        ];
        $this->attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $actual = $this->decimal->getAggregationData($this->attribute, $bucketName);
        $this->assertEquals($expected, $actual);
    }
}
