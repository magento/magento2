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
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_persistentSession = $this->_objectManager->get('Magento\Persistent\Helper\Session');
        $this->_customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
        $this->_model = $this->_objectManager->create(
            'Magento\Persistent\Model\Observer\Session',
            [
                'persistentSession' => $this->_persistentSession,
                'customerSession' => $this->_customerSession
            ]
        );
    }

    /**
     * @covers \Magento\Persistent\Model\Observer\Session::synchronizePersistentOnLogin
     */
    public function testSynchronizePersistentOnLogin()
    {
        $event = new \Magento\Framework\Event();
        $observer = new \Magento\Framework\Event\Observer(['event' => $event]);

        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService */
        $customerAccountService = $this->_objectManager->create(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );

        /** @var $customer \Magento\Customer\Service\V1\Data\Customer */
        $customer = $customerAccountService->getCustomer(1);
        $event->setData('customer', $customer);
        $this->_persistentSession->setRememberMeChecked(true);
        $this->_model->synchronizePersistentOnLogin($observer);

        // check that persistent session has been stored for Customer
        /** @var \Magento\Persistent\Model\Session $sessionModel */
        $sessionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Persistent\Model\Session'
        );
        $sessionModel->loadByCustomerId(1);
        $this->assertEquals(1, $sessionModel->getCustomerId());
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
            'Magento\Persistent\Model\Session'
        );
        $sessionModel->loadByCookieKey();
        $this->assertEquals(1, $sessionModel->getCustomerId());

        $this->_customerSession->logout();

        /** @var \Magento\Persistent\Model\Session $sessionModel */
        $sessionModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Persistent\Model\Session'
        );
        $sessionModel->loadByCookieKey();
        $this->assertNull($sessionModel->getCustomerId());
    }
}
