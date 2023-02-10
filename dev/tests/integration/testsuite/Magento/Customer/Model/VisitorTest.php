<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\TestFramework\Helper\Bootstrap;

class VisitorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testBindCustomerLogin()
    {
        /** @var \Magento\Customer\Model\Visitor $visitor */
        $visitor = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Visitor::class);
        $visitor->unsCustomerId();
        $visitor->unsDoCustomerLogin();

        $customer = $this->_loginCustomer('customer@example.com', 'password');

        // Visitor has not customer ID yet
        $this->assertTrue($visitor->getDoCustomerLogin());
        $this->assertEquals($customer->getId(), $visitor->getCustomerId());

        // Visitor already has customer ID
        $visitor->unsDoCustomerLogin();
        $this->_loginCustomer('customer@example.com', 'password');
        $this->assertNull($visitor->getDoCustomerLogin());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testBindCustomerLogout()
    {
        /** @var \Magento\Customer\Model\Visitor $visitor */
        $visitor = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Visitor::class);

        $this->_loginCustomer('customer@example.com', 'password');
        $visitor->setCustomerId(1);
        $visitor->unsDoCustomerLogout();
        $this->_logoutCustomer(1);

        // Visitor has customer ID => check that do_customer_logout flag is set
        $this->assertTrue($visitor->getDoCustomerLogout());

        $this->_loginCustomer('customer@example.com', 'password');
        $visitor->unsCustomerId();
        $visitor->unsDoCustomerLogout();
        $this->_logoutCustomer(1);

        // Visitor has no customer ID => check that do_customer_logout flag not changed
        $this->assertNull($visitor->getDoCustomerLogout());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testClean()
    {
        $customerIdNow = '1';
        $createdAtNow = date('Y-m-d H:i:s', time());
        $lastVisitNow = date('Y-m-d H:i:s', time());
        $customerIdPast = null;
        $createdAtPast = date('Y-m-d H:i:s', time() - 172800);
        $lastVisitPast = date('Y-m-d H:i:s', time() - 172800);

        /** @var \Magento\Customer\Model\Visitor $visitor */
        $visitor = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Visitor::class);
        $visitor->setCustomerId($customerIdPast);
        $visitor->setCreatedAt($createdAtPast);
        $visitor->setLastVisitAt($lastVisitPast);
        $visitor->save();
        $visitorIdPast = $visitor->getId();
        $visitor->unsetData();
        $visitor->setCustomerId($customerIdNow);
        $visitor->setCreatedAt($createdAtNow);
        $visitor->setLastVisitAt($lastVisitNow);
        $visitor->save();
        $visitorIdNow = $visitor->getId();
        $visitor->unsetData();

        $visitor->clean();
        $visitor->load($visitorIdPast);
        $this->assertEquals([], $visitor->getData());
        $visitor->unsetData();
        $visitor->load($visitorIdNow);
        $result = $visitor->getData();
        $this->assertEquals($visitorIdNow, $result['visitor_id']);
        $this->assertEquals($customerIdNow, $result['customer_id']);
        $this->assertGreaterThanOrEqual(strtotime($createdAtNow), strtotime($result['created_at']));
        $this->assertEquals($lastVisitNow, $result['last_visit_at']);
        $this->assertNull($result['session_id']);
    }

    /**
     * Authenticate customer and return its DTO
     * @param string $username
     * @param string $password
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function _loginCustomer($username, $password)
    {
        /** @var \Magento\Customer\Api\AccountManagementInterface $accountManagement */
        $accountManagement = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        return $accountManagement->authenticate($username, $password);
    }

    /**
     * Log out customer
     * @param int $customerId
     */
    public function _logoutCustomer($customerId)
    {
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Session::class);
        $customerSession->setCustomerId($customerId);
        $customerSession->logout();
    }
}
