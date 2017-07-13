<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Command;

use Braintree\IsNode;
use Braintree\MultipleValueNode;
use Braintree\TextNode;
use Magento\Braintree\Gateway\Command\CaptureStrategyCommand;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeSearchAdapter;

/**
 * Class CaptureStrategyCommandTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CaptureStrategyCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CaptureStrategyCommand
     */
    private $strategyCommand;

    /**
     * @var CommandPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var GatewayCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $command;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    /**
     * @var BraintreeAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $braintreeAdapter;

    /**
     * @var BraintreeSearchAdapter
     */
    private $braintreeSearchAdapter;

    protected function setUp()
    {
        $this->commandPool = $this->getMockBuilder(CommandPoolInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', '__wakeup'])
            ->getMock();

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->initCommandMock();
        $this->initTransactionRepositoryMock();
        $this->initFilterBuilderMock();
        $this->initSearchCriteriaBuilderMock();

        $this->braintreeAdapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->braintreeSearchAdapter = new BraintreeSearchAdapter();

        $this->strategyCommand = new CaptureStrategyCommand(
            $this->commandPool,
            $this->transactionRepository,
            $this->filterBuilder,
            $this->searchCriteriaBuilder,
            $this->subjectReaderMock,
            $this->braintreeAdapter,
            $this->braintreeSearchAdapter
        );
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\CaptureStrategyCommand::execute
     */
    public function testSaleExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);

        $this->payment->expects(static::once())
            ->method('getAuthorizationTransaction')
            ->willReturn(false);

        $this->payment->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $this->buildSearchCriteria();

        $this->transactionRepository->expects(static::once())
            ->method('getTotalCount')
            ->willReturn(0);

        $this->commandPool->expects(static::once())
            ->method('get')
            ->with(CaptureStrategyCommand::SALE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\CaptureStrategyCommand::execute
     */
    public function testCaptureExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;
        $lastTransId = 'txnds';

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);

        $this->payment->expects(static::once())
            ->method('getAuthorizationTransaction')
            ->willReturn(true);
        $this->payment->expects(static::once())
            ->method('getLastTransId')
            ->willReturn($lastTransId);

        $this->payment->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $this->buildSearchCriteria();

        $this->transactionRepository->expects(static::once())
            ->method('getTotalCount')
            ->willReturn(0);

        // authorization transaction was not expired
        $collection = $this->getNotExpiredExpectedCollection($lastTransId);
        $collection->expects(static::once())
            ->method('maximumCount')
            ->willReturn(0);

        $this->commandPool->expects(static::once())
            ->method('get')
            ->with(CaptureStrategyCommand::CAPTURE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * @param string $lastTransactionId
     * @return \Braintree\ResourceCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getNotExpiredExpectedCollection($lastTransactionId)
    {
        $isExpectations = [
            'id' => ['is' => $lastTransactionId],
            'status' => [\Braintree\Transaction::AUTHORIZATION_EXPIRED]
        ];

        $collection = $this->getMockBuilder(\Braintree\ResourceCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->braintreeAdapter->expects(static::once())
            ->method('search')
            ->with(
                static::callback(
                    function (array $filters) use ($isExpectations) {
                        foreach ($filters as $filter) {
                            /** @var IsNode $filter */
                            if (!isset($isExpectations[$filter->name])) {
                                return false;
                            }

                            if ($isExpectations[$filter->name] !== $filter->toParam()) {
                                return false;
                            }
                        }

                        return true;
                    }
                )
            )
            ->willReturn($collection);

        return $collection;
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\CaptureStrategyCommand::execute
     */
    public function testExpiredAuthorizationPerformVaultCaptureExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;
        $lastTransId = 'txnds';

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);

        $this->payment->expects(static::once())
            ->method('getAuthorizationTransaction')
            ->willReturn(true);
        $this->payment->expects(static::once())
            ->method('getLastTransId')
            ->willReturn($lastTransId);

        $this->payment->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $this->buildSearchCriteria();

        $this->transactionRepository->expects(static::once())
            ->method('getTotalCount')
            ->willReturn(0);

        // authorization transaction was expired
        $collection = $this->getNotExpiredExpectedCollection($lastTransId);
        $collection->expects(static::once())
            ->method('maximumCount')
            ->willReturn(1);

        $this->commandPool->expects(static::once())
            ->method('get')
            ->with(CaptureStrategyCommand::VAULT_CAPTURE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * @covers \Magento\Braintree\Gateway\Command\CaptureStrategyCommand::execute
     */
    public function testVaultCaptureExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);

        $this->payment->expects(static::once())
            ->method('getAuthorizationTransaction')
            ->willReturn(true);

        $this->payment->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $this->buildSearchCriteria();

        $this->transactionRepository->expects(static::once())
            ->method('getTotalCount')
            ->willReturn(1);

        $this->commandPool->expects(static::once())
            ->method('get')
            ->with(CaptureStrategyCommand::VAULT_CAPTURE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * Create mock for payment data object and order payment
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        return $mock;
    }

    /**
     * Create mock for gateway command object
     */
    private function initCommandMock()
    {
        $this->command = $this->getMockBuilder(GatewayCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $this->command->expects(static::once())
            ->method('execute')
            ->willReturn([]);
    }

    /**
     * Create mock for filter object
     */
    private function initFilterBuilderMock()
    {
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setField', 'setValue', 'create', '__wakeup'])
            ->getMock();
    }

    /**
     * Build search criteria
     */
    private function buildSearchCriteria()
    {
        $this->filterBuilder->expects(static::exactly(2))
            ->method('setField')
            ->willReturnSelf();
        $this->filterBuilder->expects(static::exactly(2))
            ->method('setValue')
            ->willReturnSelf();

        $searchCriteria = new SearchCriteria();
        $this->searchCriteriaBuilder->expects(static::exactly(2))
            ->method('addFilters')
            ->willReturnSelf();
        $this->searchCriteriaBuilder->expects(static::once())
            ->method('create')
            ->willReturn($searchCriteria);

        $this->transactionRepository->expects(static::once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturnSelf();
    }

    /**
     * Create mock for search criteria object
     */
    private function initSearchCriteriaBuilderMock()
    {
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilters', 'create', '__wakeup'])
            ->getMock();
    }

    /**
     * Create mock for transaction repository
     */
    private function initTransactionRepositoryMock()
    {
        $this->transactionRepository = $this->getMockBuilder(TransactionRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', 'getTotalCount', 'delete', 'get', 'save', 'create', '__wakeup'])
            ->getMock();
    }
}
