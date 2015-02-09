<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected function setUp()
    {
        $this->_customerSession = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Session'
        );
    }

    public function testLoginById()
    {
        $this->assertTrue($this->_customerSession->loginById(1));
        // fixture
        $this->assertTrue($this->_customerSession->isLoggedIn());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testLoginByIdCustomerDataLoadedCorrectly()
    {
        $fixtureCustomerId = 1;

        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $customerSession->loginById($fixtureCustomerId);

        $customerData = $customerSession->getCustomerData();

        $this->assertEquals($fixtureCustomerId, $customerData->getId(), "Customer data was loaded incorrectly");
    }
}
