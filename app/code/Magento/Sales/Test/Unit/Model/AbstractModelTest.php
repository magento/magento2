<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

/**
 * Class AbstractModelTest
 */
class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $model;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Sales\Model\Order');
    }

    public function testGetEventPrefix()
    {
        $this->assertEquals('sales_order', $this->model->getEventPrefix());
    }

    public function testGetEventObject()
    {
        $this->assertEquals('order', $this->model->getEventObject());
    }

    public function testDataManagement()
    {
        $this->model->setData('code-1', 'value-1');
        $this->assertEquals('value-1', $this->model->getData('code-1'));
        $this->model->flushDataIntoModel();
        $this->model->setData('code-1', 'value-2');
        $this->assertEquals('value-2', $this->model->getData('code-1'));
        $this->model->setData('code-2', 'value-3');
        $this->assertEquals(['code-1' => 'value-2', 'code-2' => 'value-3'], $this->model->getData());
        $this->model->unsetData('code-1');
        $this->assertEquals(null, $this->model->getData('code-1'));
    }
}
