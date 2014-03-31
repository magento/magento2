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
namespace Magento\Customer\Helper;

use Magento\TestFramework\Helper\Bootstrap;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Helper\Data */
    protected $_dataHelper;

    /** @var \Magento\Customer\Model\Session */
    protected $_customerSession;

    protected function setUp()
    {
        $this->_dataHelper = Bootstrap::getObjectManager()->create('Magento\Customer\Helper\Data');
        $this->_customerSession = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Session');
        $this->_customerSession->setCustomerId(1);
        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerData()
    {
        $this->assertInstanceOf('\Magento\Customer\Service\V1\Data\Customer', $this->_dataHelper->getCustomerData());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerHasNoAddresses()
    {
        $this->assertFalse($this->_dataHelper->customerHasAddresses());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testCustomerHasAddresses()
    {
        $this->assertTrue($this->_dataHelper->customerHasAddresses());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testConfirmationNotRequired()
    {
        $this->assertFalse($this->_dataHelper->isConfirmationRequired());
    }
}
