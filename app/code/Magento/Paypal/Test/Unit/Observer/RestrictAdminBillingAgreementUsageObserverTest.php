<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement;
use Magento\Paypal\Observer\RestrictAdminBillingAgreementUsageObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RestrictAdminBillingAgreementUsageObserverTest extends TestCase
{
    /**
     * @var RestrictAdminBillingAgreementUsageObserver
     */
    protected $_model;

    /**
     * @var Observer
     */
    protected $_observer;

    /**
     * @var DataObject
     */
    protected $_event;

    /**
     * @var AuthorizationInterface|MockObject
     */
    protected $_authorization;

    protected function setUp(): void
    {
        $this->_event = new DataObject();

        $this->_observer = new Observer();
        $this->_observer->setEvent($this->_event);

        $this->_authorization = $this->getMockForAbstractClass(AuthorizationInterface::class);

        $this->_model = new RestrictAdminBillingAgreementUsageObserver($this->_authorization);
    }

    /**
     * @return array
     */
    public static function restrictAdminBillingAgreementUsageDataProvider()
    {
        return [
            [new \stdClass(), false, true],
            [
                static fn (self $testCase) => $testCase->getMockForAbstractClass(
                    AbstractAgreement::class,
                    [],
                    '',
                    false
                ),
                true,
                true
            ],
            [
                static fn (self $testCase) => $testCase->getMockForAbstractClass(
                    AbstractAgreement::class,
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
        if (is_callable($methodInstance)) {
            $methodInstance = $methodInstance($this);
        }
        $this->_event->setMethodInstance($methodInstance);
        $this->_authorization->expects(
            $this->any()
        )->method(
            'isAllowed'
        )->with(
            'Magento_Paypal::use'
        )->willReturn(
            $isAllowed
        );
        $result = new DataObject();
        $result->setData('is_available', true);
        $this->_event->setResult($result);
        $this->_model->execute($this->_observer);
        $this->assertEquals($isAvailable, $result->getData('is_available'));
    }
}
