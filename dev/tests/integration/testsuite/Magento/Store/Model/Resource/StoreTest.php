<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Store\Model\Resource;

class StoreTest extends \PHPUnit_Framework_TestCase
{
    public function testCountAll()
    {
        /** @var $model \Magento\Store\Model\Resource\Store */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Store\Model\Resource\Store'
        );
        $this->assertEquals(1, $model->countAll());
        $this->assertEquals(1, $model->countAll(false));
        $this->assertEquals(2, $model->countAll(true));
    }
}
