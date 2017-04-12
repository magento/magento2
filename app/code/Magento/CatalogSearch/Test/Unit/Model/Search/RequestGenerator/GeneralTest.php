<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\RequestGenerator;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Search\RequestGenerator\General;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\FilterInterface;

class GeneralTest extends \PHPUnit_Framework_TestCase
{
    /** @var  General */
    private $general;

    /** @var  Attribute|\PHPUnit_Framework_MockObject_MockObject */
    private $attribute;

    protected function setUp()
    {
        $this->attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
