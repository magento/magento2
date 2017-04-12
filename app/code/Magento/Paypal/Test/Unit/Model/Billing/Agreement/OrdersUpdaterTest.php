<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Billing\Agreement;

class OrdersUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrdersUpdater
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_registry;

    /**
     * @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_agreementResource;

    protected function setUp()
    {
        $this->_registry = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $this->_agreementResource = $this->getMock(
            \Magento\Paypal\Model\ResourceModel\Billing\Agreement::class,
            [],
            [],
            '',
            false
        );

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            \Magento\Paypal\Model\Billing\Agreement\OrdersUpdater::class,
            ['coreRegistry' => $this->_registry, 'agreementResource' => $this->_agreementResource]
        );
    }

    public function testUpdate()
    {
        $agreement = $this->getMock(\Magento\Paypal\Model\Billing\Agreement::class, [], [], '', false);
        $argument = $this->getMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class, [], [], '', false);

        $this->_registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_billing_agreement'
        )->will(
            $this->returnValue($agreement)
        );

        $agreement->expects($this->once())->method('getId')->will($this->returnValue('agreement id'));
        $this->_agreementResource->expects(
            $this->once()
        )->method(
            'addOrdersFilter'
        )->with(
            $this->identicalTo($argument),
            'agreement id'
        );

        $this->assertSame($argument, $this->_model->update($argument));
    }

    /**
     * @expectedException \DomainException
     */
    public function testUpdateWhenBillingAgreementIsNotSet()
    {
        $this->_registry->expects(
            $this->once()
        )->method(
            'registry'
        )->with(
            'current_billing_agreement'
        )->will(
            $this->returnValue(null)
        );

        $this->_model->update('any argument');
    }
}
