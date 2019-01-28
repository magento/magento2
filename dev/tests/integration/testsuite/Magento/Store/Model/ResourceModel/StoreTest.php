<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\ResourceModel;

class StoreTest extends \PHPUnit\Framework\TestCase
{
    public function testCountAll()
    {
        /** @var $model \Magento\Store\Model\ResourceModel\Store */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Store\Model\ResourceModel\Store::class
        );
        $this->assertSame(1, $model->countAll());
        $this->assertSame(1, $model->countAll(false));
        $this->assertSame(2, $model->countAll(true));
    }
}
