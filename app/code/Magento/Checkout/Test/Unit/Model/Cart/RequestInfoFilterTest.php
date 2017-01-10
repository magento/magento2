<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model\Cart;

/**
 * Class RequestInfoFilterTest
 */
class RequestInfoFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Model\Cart\RequestInfoFilter
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $this->objectManager->getObject(
            \Magento\Checkout\Model\Cart\RequestInfoFilter::class,
            [
                'filterList' => ['efg', 'xyz'],
            ]
        );
    }

    /**
     * Test Filter method
     */
    public function testFilter()
    {
        /** @var \Magento\Framework\DataObject $params */
        $params = $this->objectManager->getObject(
            \Magento\Framework\DataObject::class,
            ['data' => ['abc' => 1, 'efg' => 1, 'xyz' => 1]]
        );
        $result = $this->model->filter($params);
        $this->assertEquals($this->model, $result);
        $this->assertEquals(['abc' => 1], $params->convertToArray());
    }
}
