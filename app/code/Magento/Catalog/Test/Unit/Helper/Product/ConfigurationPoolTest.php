<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper\Product;

use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ConfigurationPoolTest extends TestCase
{
    const INSTANCES_TYPE = ['simple' => 'simple', 'default' => 'default'];

    /**
     * @var ConfigurationPool
     */
    private $model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->model = $objectManager->getObject(
            ConfigurationPool::class,
            [
                'objectManager' => $objectManagerMock,
                'instancesByType' => static::INSTANCES_TYPE
            ]
        );
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
