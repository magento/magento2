<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\Error test case
 */
class ErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\Error
     */
    protected $model;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Framework\Message\Error');
    }

    public function testGetType()
    {
        $this->assertEquals(MessageInterface::TYPE_ERROR, $this->model->getType());
    }
}
