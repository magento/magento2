<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Persistent\Model\Observer;

/**
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Observer\Session
     */
    protected $_model;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession;

    /**
     * @var \Magento\Stdlib\Cookie|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cookieMock;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_persistentSession = $this->_objectManager->get('Magento\Persistent\Helper\Session');
        $this->_cookieMock = $this->getMock('Magento\Stdlib\Cookie', array('set'), array(), '', false);
        $this->_customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $this->_model = $this->_objectManager->create('Magento\Persistent\Model\Observer\Session', array(
            'persistentSession' => $this->_persistentSession,
            'cookie'            => $this->_cookieMock,
            'customerSession'   => $this->_customerSession
        ));
    }

    /**
     * @covers \Magento\Persistent\Model\Observer\Session::synchronizePersistentOnLogin
     */
    public function testSynchronizePersistentOnLogin()
    {
        $event = new \Magento\Event;
        $observer = new \Magento\Event\Observer(array('event' => $event));

        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load(1);
        $event->setData('customer', $customer);
        $this->_persistentSession->setRememberMeChecked(true);
        $this->_cookieMock->expects($this->once())->method('set')->with(
            \Magento\Persistent\Model\Session::COOKIE_NAME,
            $this->anything(),
            $this->anything(),
            $this->_customerSession->getCookiePath()
        );
        $this->_model->synchronizePersistentOnLogin($observer);
    }
}
