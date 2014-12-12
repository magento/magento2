<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\CollectionFactory test case
 */
class CollectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\CollectionFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create('Magento\Framework\Message\CollectionFactory');
    }

    public function testCreate()
    {
        $message = $this->model->create();
        $this->assertInstanceOf('\Magento\Framework\Message\Collection', $message);
    }
}
