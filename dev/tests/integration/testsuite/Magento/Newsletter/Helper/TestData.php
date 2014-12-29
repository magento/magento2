<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Newsletter\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Magento\Newsletter\Model\Subscriber
     */
    protected $_subscriber;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_subscriber = $this->_objectManager->get('Magento\Newsletter\Model\Subscriber');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetConfirmationUrl()
    {
        $url = $this->_objectManager->get('Magento\Newsletter\Helper\Data')->getConfirmationUrl($this->_subscriber);
        $this->assertFalse(strpos($url, "admin"));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetUnsubscribeUrl()
    {
        $url = $this->_objectManager->get('Magento\Newsletter\Helper\Data')->getConfirmationUrl($this->_subscriber);
        $this->assertFalse(strpos($url, "admin"));
    }

}
