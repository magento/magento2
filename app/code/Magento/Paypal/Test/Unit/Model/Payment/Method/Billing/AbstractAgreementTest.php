<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payment\Method\Billing;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Paypal\Model\Billing\Agreement;
use Magento\Paypal\Model\Billing\AgreementFactory;
use Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractAgreementTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var AgreementFactory|MockObject
     */
    private $agreementFactory;

    /**
     * @var AbstractAgreementStub
     */
    private $payment;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->eventManagerMock = $this->createMock(ManagerInterface::class);

        $this->agreementFactory = $this->createMock(AgreementFactory::class);

        $objects = [
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $helper->prepareObjectManager($objects);
        $this->payment = $helper->getObject(
            AbstractAgreementStub::class,
            [
                'eventDispatcher' => $this->eventManagerMock,
                'agreementFactory' => $this->agreementFactory
            ]
        );
    }

    public function testAssignData()
    {
        $baId = '1678235';
        $customerId = 67;
        $referenceId = '1234124';

        $data = new DataObject(
            [
                PaymentInterface::KEY_ADDITIONAL_DATA => [
                    AbstractAgreement::TRANSPORT_BILLING_AGREEMENT_ID => $baId
                ]
            ]
        );
        $paymentInfo = $this->createMock(Payment::class);
        $quote = $this->createPartialMockWithReflection(
            Quote::class,
            ['getCustomerId']
        );

        $this->payment->setInfoInstance($paymentInfo);
        $this->parentAssignDataExpectation($data);

        $agreementModel = $this->createPartialMockWithReflection(
            Agreement::class,
            ['load', 'getId', 'getCustomerId', 'getReferenceId']
        );

        $this->agreementFactory->expects(static::once())
            ->method('create')
            ->willReturn($agreementModel);

        $paymentInfo->expects(static::once())
            ->method('getQuote')
            ->willReturn($quote);

        $agreementModel->expects(static::once())
            ->method('load')
            ->with($baId);
        $agreementModel->expects(static::once())
            ->method('getId')
            ->willReturn($baId);
        $agreementModel->expects(static::atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $agreementModel->expects(static::atLeastOnce())
            ->method('getReferenceId')
            ->willReturn($referenceId);

        $quote->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $paymentInfo->expects(static::exactly(2))
            ->method('setAdditionalInformation')
            ->willReturnMap(
                [
                    [AbstractAgreement::TRANSPORT_BILLING_AGREEMENT_ID, $baId],
                    [AbstractAgreement::PAYMENT_INFO_REFERENCE_ID, $referenceId]
                ]
            );

        $this->payment->assignData($data);
    }

    /**
     * @param DataObject $data
     * @throws LocalizedException
     */
    private function parentAssignDataExpectation(DataObject $data)
    {
        $eventData = [
            AbstractDataAssignObserver::METHOD_CODE => $this,
            AbstractDataAssignObserver::MODEL_CODE => $this->payment->getInfoInstance(),
            AbstractDataAssignObserver::DATA_CODE => $data
        ];

        $this->eventManagerMock->expects(static::exactly(2))
            ->method('dispatch')
            ->willReturnMap(
                [
                    [
                        'payment_method_assign_data_' . AbstractAgreementStub::STUB_CODE,
                        $eventData
                    ],
                    [
                        'payment_method_assign_data',
                        $eventData
                    ]
                ]
            );
    }
}
