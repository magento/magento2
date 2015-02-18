<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $helper;

    protected function setUp()
    {
        $this->helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Pricing\Helper\Data'
        );
    }

    public function testCurrency()
    {
        $price = 10.00;
        $priceHtml = '<span class="price">$10.00</span>';
        $this->assertEquals($priceHtml, $this->helper->currency($price));
    }
}
