<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
        $this->assertEquals(1, $model->countAll());
        $this->assertEquals(1, $model->countAll(false));
        $this->assertEquals(2, $model->countAll(true));
    }
}
