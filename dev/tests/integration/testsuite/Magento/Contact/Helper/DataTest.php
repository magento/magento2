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

namespace Magento\Contact\Helper;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\Contact\Helper\Data
 *
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data
     */
    protected $contactsHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Setup customer data
     */
    protected function setUp()
    {
        $customerIdFromFixture = 1;
        $this->contactsHelper = Bootstrap::getObjectManager()->create('Magento\Contact\Helper\Data');
        $this->customerSession = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Session');
        /**
         * @var $customerService \Magento\Customer\Service\V1\CustomerAccountServiceInterface
         */
        $customerService = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $customerData = $customerService->getCustomer($customerIdFromFixture);
        $this->customerSession->setCustomerDataObject($customerData);
    }

    /**
     * Verify if username is set in session
     */
    public function testGetUserName()
    {
        $this->assertEquals('Firstname Lastname', $this->contactsHelper->getUserName());
    }

    /**
     * Verify if user email is set in session
     */
    public function testGetEmail()
    {
        $this->assertEquals('customer@example.com', $this->contactsHelper->getUserEmail());
    }

}
