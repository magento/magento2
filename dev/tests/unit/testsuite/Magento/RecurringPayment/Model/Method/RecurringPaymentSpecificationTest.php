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
namespace Magento\RecurringPayment\Model\Method;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class RecurringPaymentSpecificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\RecurringPayment\Model\Method\RecurringPaymentSpecification */
    protected $recurringPaymentSpecification;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Payment\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Payment\Model\Config', array(), array(), '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testIsSatisfiedBy()
    {
        $paymentMethodCode = 'test';
        $this->configMock->expects(
            $this->once()
        )->method(
            'getMethodsInfo'
        )->will(
            $this->returnValue(
                array(
                    $paymentMethodCode => array(
                        \Magento\RecurringPayment\Model\Method\RecurringPaymentSpecification::CONFIG_KEY => 1
                    )
                )
            )
        );

        $this->recurringPaymentSpecification = $this->objectManagerHelper->getObject(
            'Magento\RecurringPayment\Model\Method\RecurringPaymentSpecification',
            array('paymentConfig' => $this->configMock)
        );

        $this->assertTrue($this->recurringPaymentSpecification->isSatisfiedBy($paymentMethodCode));
    }
}
