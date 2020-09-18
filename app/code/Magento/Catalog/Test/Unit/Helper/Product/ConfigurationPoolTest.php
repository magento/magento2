<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper\Product;

use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class ConfigurationPoolTest extends TestCase
{
    /**
     * @var array
     */
    protected $instancesType;

    /**
     * @var ConfigurationPool
     */
    protected $model;

    protected function setUp(): void
    {
        $this->instancesType = ['simple' => 'simple', 'default' => 'default'];

        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->model = new ConfigurationPool($objectManagerMock, $this->instancesType);
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
