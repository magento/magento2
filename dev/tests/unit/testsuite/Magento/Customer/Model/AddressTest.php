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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    const ORIG_CUSTOMER_ID = 1;
    const ORIG_PARENT_ID = 2;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $address;

    /**
     * @var \Magento\Customer\Model\Customer | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Model\CustomerFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactory;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->customer = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::ORIG_CUSTOMER_ID));
        $this->customer->expects($this->any())
            ->method('load')
            ->will($this->returnSelf());

        $this->customerFactory = $this->getMockBuilder('Magento\Customer\Model\CustomerFactory')
            ->setMethods(['create'])
            ->getMock();
        $this->customerFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->customer));

        $this->resource = $this->getMockBuilder('Magento\Customer\Model\Resource\Address')
            ->disableOriginalConstructor()
            ->getMock();

        $this->address = $this->objectManager->getObject(
            'Magento\Customer\Model\Address',
            [
                'customerFactory' => $this->customerFactory,
                'resource' => $this->resource,
            ]
        );
    }

    public function testCustomerId()
    {
        $this->address->setParentId(self::ORIG_PARENT_ID);
        $this->assertEquals(self::ORIG_PARENT_ID, $this->address->getCustomerId());

        $this->address->setCustomerId(self::ORIG_CUSTOMER_ID);
        $this->assertEquals(self::ORIG_CUSTOMER_ID, $this->address->getCustomerId());
    }

    public function testCustomer()
    {
        $this->address->unsetData('cusomer_id');
        $this->assertFalse($this->address->getCustomer());

        $this->address->setCustomerId(self::ORIG_CUSTOMER_ID);

        $customer = $this->address->getCustomer();
        $this->assertEquals(self::ORIG_CUSTOMER_ID, $customer->getId());

        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $customer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::ORIG_CUSTOMER_ID + 1));

        $this->address->setCustomer($customer);
        $this->assertEquals(self::ORIG_CUSTOMER_ID + 1, $this->address->getCustomerId());
    }

    public function testGetAttributes()
    {
        $resultValue = 'test';

        $this->resource->expects($this->any())
            ->method('loadAllAttributes')
            ->will($this->returnSelf());
        $this->resource->expects($this->any())
            ->method('getSortedAttributes')
            ->will($this->returnValue($resultValue));

        $this->assertEquals($resultValue, $this->address->getAttributes());
    }

    public function testGetEntityTypeId()
    {
        $mockEntityType = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $mockEntityType->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::ORIG_CUSTOMER_ID));

        $this->resource->expects($this->any())
            ->method('getEntityType')
            ->will($this->returnValue($mockEntityType));

        $this->assertEquals(self::ORIG_CUSTOMER_ID, $this->address->getEntityTypeId());
    }

    public function testRegionId()
    {
        $this->address->setRegionId(1);
        $this->assertEquals(1, $this->address->getRegionId());
    }
}
