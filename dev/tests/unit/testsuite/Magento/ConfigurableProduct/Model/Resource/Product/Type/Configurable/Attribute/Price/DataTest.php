<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
