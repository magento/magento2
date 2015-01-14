<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Price;

/**
 * Class DataTest
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Test for setProductPrice and getProductPrice data
     */
    public function testGetProductPrice()
    {
        $productId = 1;
        $productPrice = ['pricing' => 'test'];
        $priceData = new Data();
        $priceData->setProductPrice($productId, $productPrice);
        $this->assertEquals($productPrice, $priceData->getProductPrice($productId));
    }
}
