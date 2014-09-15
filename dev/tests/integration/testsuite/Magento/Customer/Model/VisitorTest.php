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
namespace Magento\Customer\Model;

use Magento\TestFramework\Helper\Bootstrap;

class VisitorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testBindCustomerLogin()
    {
        /** @var \Magento\Customer\Model\Visitor $visitor */
        $visitor = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Visitor');
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
        $visitor = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Visitor');

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
     * Authenticate customer and return its DTO
     * @param string $username
     * @param string $password
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    protected function _loginCustomer($username, $password)
    {
        /** @var \Magento\Customer\Service\V1\CustomerAccountService $service */
        $service = Bootstrap::getObjectManager()->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        return $service->authenticate($username, $password);
    }

    /**
     * Log out customer
     * @param int $customerId
     */
    public function _logoutCustomer($customerId)
    {
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $customerSession->setCustomerId($customerId);
        $customerSession->logout();
    }
}
