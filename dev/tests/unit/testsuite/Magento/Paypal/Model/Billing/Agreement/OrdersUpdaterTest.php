<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Model\Billing\Agreement;

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
     * @var \Magento\Paypal\Model\Resource\Billing\Agreement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_agreementResource;

    protected function setUp()
    {
        $this->_registry = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $this->_agreementResource = $this->getMock(
            '\Magento\Paypal\Model\Resource\Billing\Agreement',
            [],
            [],
            '',
            false
        );

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\Paypal\Model\Billing\Agreement\OrdersUpdater',
            ['coreRegistry' => $this->_registry, 'agreementResource' => $this->_agreementResource]
        );
    }

    public function testUpdate()
    {
        $agreement = $this->getMock('Magento\Paypal\Model\Billing\Agreement', [], [], '', false);
        $argument = $this->getMock('Magento\Sales\Model\Resource\Order\Collection', [], [], '', false);

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
