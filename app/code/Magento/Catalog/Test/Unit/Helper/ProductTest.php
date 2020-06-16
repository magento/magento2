<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $this->assertEquals($this->_productHelper->isDataForPriceIndexerWasChanged($data), $result);
    }

    /**
     * Data provider for testIsDataForPriceIndexerWasChanged
     * @return array
     */
    public function getData()
    {
        $product1 = $this->getMockBuilder(
            \Magento\Catalog\Model\Product::class
        )->disableOriginalConstructor()
            ->getMock();

        $product2 = $this->getMockBuilder(
            \Magento\Catalog\Model\Product::class
        )->disableOriginalConstructor()
            ->getMock();

        $product2->expects(
            $this->once()
        )->method(
            'getData'
        )->with(
            'attribute'
        )->willReturn(
            true
        );

        $product3 = $this->getMockBuilder(
            \Magento\Catalog\Model\Product::class
        )->disableOriginalConstructor()
            ->getMock();
        $product3->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'attribute'
        )->willReturn(
            true
        );

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
