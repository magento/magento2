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
namespace Magento\Customer\Service\V1\Data;

/**
 * CustomerDetails Test
 */
class CustomerDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * CustomerDetails
     *
     * @var CustomerDetails
     */
    private $_customerDetails;

    /**
     * Customer mock
     *
     * @var Customer | \PHPUnit_Framework_MockObject_MockObject
     */
    private $_customerMock;

    /**
     * Address mock
     *
     * @var Address | \PHPUnit_Framework_MockObject_MockObject
     */
    private $_addressMock;

    public function setUp()
    {
        $this->_customerMock = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Customer'
        )->disableOriginalConstructor()->getMock();
        $this->_addressMock = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Address'
        )->disableOriginalConstructor()->getMock();
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var Magento\Customer\Service\V1\Data\CustomerDetailsBuilder $customerDetailsBuilder */
        $customerDetailsBuilder = $objectManager->getObject('Magento\Customer\Service\V1\Data\CustomerDetailsBuilder');
        $customerDetailsBuilder->setCustomer(
            $this->_customerMock
        )->setAddresses(
            array($this->_addressMock, $this->_addressMock)
        );
        $this->_customerDetails = new CustomerDetails($customerDetailsBuilder);
    }

    public function testGetCustomer()
    {
        $this->assertEquals($this->_customerMock, $this->_customerDetails->getCustomer());
    }

    public function testGetAddresses()
    {
        $this->assertEquals(array($this->_addressMock, $this->_addressMock), $this->_customerDetails->getAddresses());
    }
}
