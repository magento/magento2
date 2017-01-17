<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Framework\DataObject;

/**
 * Class RestrictAdminBillingAgreementUsageObserverTest
 */
class RestrictAdminBillingAgreementUsageObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Observer\RestrictAdminBillingAgreementUsageObserver
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_observer;

    /**
     * @var DataObject
     */
    protected $_event;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authorization;

    protected function setUp()
    {
        $this->_event = new DataObject();

        $this->_observer = new \Magento\Framework\Event\Observer();
        $this->_observer->setEvent($this->_event);

        $this->_authorization = $this->getMockForAbstractClass(\Magento\Framework\AuthorizationInterface::class);

        $this->_model = new \Magento\Paypal\Observer\RestrictAdminBillingAgreementUsageObserver($this->_authorization);
    }

    public function restrictAdminBillingAgreementUsageDataProvider()
    {
        return [
            [new \stdClass(), false, true],
            [
                $this->getMockForAbstractClass(
                    \Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement::class,
                    [],
                    '',
                    false
                ),
                true,
                true
            ],
            [
                $this->getMockForAbstractClass(
                    \Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement::class,
                    [],
                    '',
                    false
                ),
                false,
                false
            ]
        ];
    }

    /**
     * @param object $methodInstance
     * @param bool $isAllowed
     * @param bool $isAvailable
     * @dataProvider restrictAdminBillingAgreementUsageDataProvider
     */
    public function testExecute($methodInstance, $isAllowed, $isAvailable)
    {
        $this->_event->setMethodInstance($methodInstance);
        $this->_authorization->expects(
            $this->any()
        )->method(
                'isAllowed'
            )->with(
                'Magento_Paypal::use'
            )->will(
                $this->returnValue($isAllowed)
            );
        $result = new DataObject();
        $result->setData('is_available', true);
        $this->_event->setResult($result);
        $this->_model->execute($this->_observer);
        $this->assertEquals($isAvailable, $result->getData('is_available'));
    }
}
