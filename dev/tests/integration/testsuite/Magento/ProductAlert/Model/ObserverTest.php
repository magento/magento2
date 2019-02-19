<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model;

/**
 * @magentoAppIsolation enabled
 */
class ObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    public function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerSession = $this->_objectManager->get(
            \Magento\Customer\Model\Session::class
        );
        $service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        $customer = $service->authenticate('customer@example.com', 'password');
        $this->_customerSession->setCustomerDataAsLoggedIn($customer);
        $this->_customerViewHelper = $this->_objectManager->create(\Magento\Customer\Helper\View::class);
    }

    /**
     * @magentoConfigFixture current_store catalog/productalert/allow_price 1
     *
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert.php
     */
    public function testProcess()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $observer = $this->_objectManager->get(\Magento\ProductAlert\Model\Observer::class);
        $observer->process();

        /** @var \Magento\TestFramework\Mail\Template\TransportBuilderMock $transportBuilder */
        $transportBuilder = $this->_objectManager->get(
            \Magento\TestFramework\Mail\Template\TransportBuilderMock::class
        );
        $this->assertContains(
            'John Smith,',
            $transportBuilder->getSentMessage()->getRawMessage()
        );
    }
}
