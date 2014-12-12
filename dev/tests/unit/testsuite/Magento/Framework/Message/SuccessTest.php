<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\Success test case
 */
class SuccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\Success
     */
    protected $model;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Framework\Message\Success');
    }

    public function testGetType()
    {
        $this->assertEquals(MessageInterface::TYPE_SUCCESS, $this->model->getType());
    }
}
