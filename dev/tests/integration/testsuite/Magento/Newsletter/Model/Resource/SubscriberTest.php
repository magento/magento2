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

namespace Magento\Newsletter\Model\Resource;

use Magento\TestFramework\Helper\Bootstrap;

class SubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Resource\Subscriber
     */
    protected $_resourceModel;

    protected function setUp()
    {
        $this->_resourceModel = Bootstrap::getObjectManager()
            ->create('Magento\Newsletter\Model\Resource\Subscriber');
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     */
    public function testLoadByCustomerDataWithCustomerId()
    {
        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService */
        $customerAccountService = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $customerData = $customerAccountService->getCustomerDetails(1)->getCustomer();
        $result = $this->_resourceModel->loadByCustomerData($customerData);

        $this->assertEquals(1, $result['customer_id']);
        $this->assertEquals('customer@example.com', $result['subscriber_email']);
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/subscribers.php
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     */
    public function testLoadByCustomerDataWithoutCustomerId()
    {
        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService */
        $customerAccountService = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $customerData = $customerAccountService->getCustomerDetails(2)->getCustomer();
        $result = $this->_resourceModel->loadByCustomerData($customerData);

        $this->assertEquals(0, $result['customer_id']);
        $this->assertEquals('customer_two@example.com', $result['subscriber_email']);
    }
}
