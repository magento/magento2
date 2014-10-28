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
        $this->_registry = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $this->_agreementResource = $this->getMock(
            '\Magento\Paypal\Model\Resource\Billing\Agreement',
            array(),
            array(),
            '',
            false
        );

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\Paypal\Model\Billing\Agreement\OrdersUpdater',
            array('coreRegistry' => $this->_registry, 'agreementResource' => $this->_agreementResource)
        );
    }

    public function testUpdate()
    {
        $agreement = $this->getMock('Magento\Paypal\Model\Billing\Agreement', array(), array(), '', false);
        $argument = $this->getMock('Magento\Sales\Model\Resource\Order\Collection', array(), array(), '', false);

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
