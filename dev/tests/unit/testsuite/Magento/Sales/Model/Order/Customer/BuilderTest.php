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
namespace Magento\Sales\Model\Order\Customer;

/**
 * Class BuilderTest
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Sales\Model\Order\Customer\Builder
     */
    protected $builder;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager',
            ['create', 'get', 'configure'],
            [],
            '',
            false
        );

        $this->builder = new \Magento\Sales\Model\Order\Customer\Builder($this->objectManagerMock);
    }

    /**
     * Run test setDob method
     */
    public function testSetDob()
    {
        $this->assertEquals($this->builder, $this->builder->setDob('dob'));
    }

    /**
     * Run test setEmail method
     */
    public function testSetEmail()
    {
        $this->assertEquals($this->builder, $this->builder->setEmail('email'));
    }

    /**
     * Run test setFirstName method
     */
    public function testSetFirstName()
    {
        $this->assertEquals($this->builder, $this->builder->setFirstName('first_name'));
    }

    /**
     * Run test setGender method
     */
    public function testSetGender()
    {
        $this->assertEquals($this->builder, $this->builder->setGender('gender'));
    }

    /**
     * Run test setGroupId method
     */
    public function testSetGroupId()
    {
        $this->assertEquals($this->builder, $this->builder->setGroupId('group_id'));
    }

    /**
     * Run test setId method
     */
    public function testSetId()
    {
        $this->assertEquals($this->builder, $this->builder->setId('id'));
    }

    /**
     * Run test setIsGuest method
     */
    public function testSetIsGuest()
    {
        $this->assertEquals($this->builder, $this->builder->setIsGuest('is_guest'));
    }

    /**
     * Run test setLastName method
     */
    public function testSetLastName()
    {
        $this->assertEquals($this->builder, $this->builder->setLastName('last_name'));
    }

    /**
     * Run test setMiddleName method
     */
    public function testSetMiddleName()
    {
        $this->assertEquals($this->builder, $this->builder->setMiddleName('middle_name'));
    }

    /**
     * Run test setNote method
     */
    public function testSetNote()
    {
        $this->assertEquals($this->builder, $this->builder->setNote('note'));
    }

    /**
     * Run test setNoteNotify method
     */
    public function testSetNoteNotify()
    {
        $this->assertEquals($this->builder, $this->builder->setNoteNotify('note_notify'));
    }

    /**
     * Run test setPrefix method
     */
    public function testSetPrefix()
    {
        $this->assertEquals($this->builder, $this->builder->setPrefix('prefix'));
    }

    /**
     * Run test setSuffix method
     */
    public function testSetSuffix()
    {
        $this->assertEquals($this->builder, $this->builder->setSuffix('suffix'));
    }

    /**
     * Run test setTaxvat method
     */
    public function testSetTaxvat()
    {
        $this->assertEquals($this->builder, $this->builder->setTaxvat('taxvat'));
    }

    /**
     * Run test create method
     */
    public function testCreate()
    {
        $customerMock = $this->getMock(
            'Magento\Sales\Model\Order\Customer',
            [],
            [],
            '',
            false
        );
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($customerMock));

        $this->assertEquals($customerMock, $this->builder->create());
    }
}
