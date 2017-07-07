<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

/**
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class SynchronizePersistentOnLogoutObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerSession = $this->_objectManager->get(\Magento\Customer\Model\Session::class);
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/logout_clear 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     */
    public function testSynchronizePersistentOnLogout()
    {
        $this->_customerSession->loginById(1);

        // check that persistent session has been stored for Customer
        /** @var \Magento\Persistent\Model\Session $sessionModel */
        $sessionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Persistent\Model\Session::class
        );
        $sessionModel->loadByCookieKey();
        $this->assertEquals(1, $sessionModel->getCustomerId());

        $this->_customerSession->logout();

        /** @var \Magento\Persistent\Model\Session $sessionModel */
        $sessionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Persistent\Model\Session::class
        );
        $sessionModel->loadByCookieKey();
        $this->assertNull($sessionModel->getCustomerId());
    }
}
