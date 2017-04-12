<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Helper\Product;

class ConfigurationPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $instancesType;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $model;

    protected function setUp()
    {
        $this->instancesType = ['simple' => 'simple', 'default' => 'default'];

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = new \Magento\Catalog\Helper\Product\ConfigurationPool($objectManagerMock, $this->instancesType);
    }

    /**
     * @dataProvider getByProductTypeDataProvider
     * @param string $productType
     * @param string $expectedResult
     */
    public function testGetByProductType($productType, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->model->getByProductType($productType));
    }

    /**
     * @return array
     */
    public function getByProductTypeDataProvider()
    {
        return [
            [
                'productType' => 'simple',
                'expectedResult' => 'simple'
            ],
            [
                'productType' => 'custom',
                'expectedResult' => 'default'
            ],
        ];
    }
}
