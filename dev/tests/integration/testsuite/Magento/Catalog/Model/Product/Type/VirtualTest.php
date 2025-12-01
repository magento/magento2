<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Product\Type;

class VirtualTest extends \PHPUnit\Framework\TestCase
{
    public function testIsVirtual()
    {
        /** @var $model \Magento\Catalog\Model\Product\Type\Virtual */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Type\Virtual::class
        );
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $this->assertTrue($model->isVirtual($product));
    }
}
