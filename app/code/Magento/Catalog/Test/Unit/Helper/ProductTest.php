<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Helper\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var Product
     */
    protected $_productHelper;

    protected function setUp(): void
    {
        $arguments = [
            'reindexPriceIndexerData' => [
                'byDataResult' => ['attribute'],
                'byDataChange' => ['attribute'],
            ],
        ];

        $objectManager = new ObjectManager($this);
        $this->_productHelper = $objectManager->getObject(Product::class, $arguments);
    }

    /**
     * @param mixed $data
     * @param boolean $result
     * @dataProvider getData
     */
    public function testIsDataForPriceIndexerWasChanged($data, $result)
    {
        if (is_callable($data)) {
            $data = $data($this);
        }
        $this->assertEquals($this->_productHelper->isDataForPriceIndexerWasChanged($data), $result);
    }

    protected function getMockForCatalogProduct($method)
    {
        $product = $this->getMockBuilder(
            \Magento\Catalog\Model\Product::class
        )->disableOriginalConstructor()
            ->getMock();
        if ($method!=null) {
            $product->expects(
                $this->once()
            )->method(
                $method
            )->with(
                'attribute'
            )->willReturn(
                true
            );
        }
        return $product;
    }
    /**
     * Data provider for testIsDataForPriceIndexerWasChanged
     * @return array
     */
    public static function getData()
    {
        $product1 = static fn (self $testCase) => $testCase->getMockForCatalogProduct(null);

        $product2 = static fn (self $testCase) => $testCase->getMockForCatalogProduct("getData");

        $product3 = static fn (self $testCase) => $testCase->getMockForCatalogProduct("dataHasChangedFor");

        return [
            [$product1, false],
            [$product2, true],
            [$product3, true],
            [['attribute' => ''], true],
            [['param' => ''], false],
            ['test', false]
        ];
    }
}
