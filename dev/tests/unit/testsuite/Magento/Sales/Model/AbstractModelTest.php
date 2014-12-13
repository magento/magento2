<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model;

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
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
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
}
