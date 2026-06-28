<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper\Product;

use PHPUnit\Framework\Attributes\DataProvider;
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

        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->model = new ConfigurationPool($objectManagerMock, $this->instancesType);
    }

    /**
     * @param string $productType
     * @param string $expectedResult
     */
    #[DataProvider('getByProductTypeDataProvider')]
    public function testGetByProductType($productType, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->model->getByProductType($productType));
    }

    /**
     * @return array
     */
    public static function getByProductTypeDataProvider()
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
