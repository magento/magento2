<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Collection;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;

/**
 * Class ProductLimitationTest
 */
class ProductLimitationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductLimitation
     */
    protected $productLimitation;

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->productLimitation = $helper->getObject(ProductLimitation::class);
    }

    public function testGetUsePriceIndex()
    {
        $this->assertFalse($this->productLimitation->isUsingPriceIndex());
        $this->productLimitation->setUsePriceIndex(true);
        $this->assertTrue($this->productLimitation->isUsingPriceIndex());
    }
}
