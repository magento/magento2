<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Collection;

use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProductLimitationTest extends TestCase
{
    /**
     * @var ProductLimitation
     */
    protected $productLimitation;

    protected function setUp(): void
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
