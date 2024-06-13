<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Store\Model\ResourceModel;

class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    public function testCountAll()
    {
        /** @var $model \Magento\Store\Model\ResourceModel\Website */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Store\Model\ResourceModel\Website::class
        );
        $this->assertEquals(1, $model->countAll());
        $this->assertEquals(1, $model->countAll(false));
        $this->assertEquals(2, $model->countAll(true));
    }
}
