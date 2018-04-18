<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Command;

use Braintree\IsNode;
use Magento\Braintree\Gateway\Command\CaptureStrategyCommand;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Braintree\Model\Adapter\BraintreeSearchAdapter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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
     * @var CommandPoolInterface|MockObject
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface|MockObject
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var GatewayCommand|MockObject
     */
    private $command;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

    /**
     * @var BraintreeAdapter|MockObject
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

        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->initCommandMock();
        $this->initTransactionRepositoryMock();
        $this->initFilterBuilderMock();
        $this->initSearchCriteriaBuilderMock();

        $this->braintreeAdapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var BraintreeAdapterFactory|MockObject $adapterFactory */
        $adapterFactory = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactory->method('create')
            ->willReturn($this->braintreeAdapter);

        $this->braintreeSearchAdapter = new BraintreeSearchAdapter();

        $this->strategyCommand = new CaptureStrategyCommand(
            $this->commandPool,
            $this->transactionRepository,
            $this->filterBuilder,
            $this->searchCriteriaBuilder,
            $this->subjectReader,
            $this->braintreeAdapter,
            $this->braintreeSearchAdapter,
            $adapterFactory
        );
    }

    public function testSaleExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->subjectReader->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);

        $this->payment->method('getAuthorizationTransaction')
            ->willReturn(false);

        $this->payment->method('getId')
            ->willReturn(1);

        $this->buildSearchCriteria();

        $this->transactionRepository->method('getTotalCount')
            ->willReturn(0);

        $this->commandPool->method('get')
            ->with(CaptureStrategyCommand::SALE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    public function testCaptureExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;
        $lastTransId = 'txnds';

        $this->subjectReader->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);

        $this->payment->method('getAuthorizationTransaction')
            ->willReturn(true);
        $this->payment->method('getLastTransId')
            ->willReturn($lastTransId);

        $this->payment->method('getId')
            ->willReturn(1);

        $this->buildSearchCriteria();

        $this->transactionRepository->method('getTotalCount')
            ->willReturn(0);

        // authorization transaction was not expired
        $collection = $this->getNotExpiredExpectedCollection($lastTransId);
        $collection->method('maximumCount')
            ->willReturn(0);

        $this->commandPool->method('get')
            ->with(CaptureStrategyCommand::CAPTURE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * @param string $lastTransactionId
     * @return \Braintree\ResourceCollection|MockObject
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

        $this->braintreeAdapter->method('search')
            ->with(
                self::callback(
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

    public function testExpiredAuthorizationPerformVaultCaptureExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;
        $lastTransId = 'txnds';

        $this->subjectReader->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);

        $this->payment->method('getAuthorizationTransaction')
            ->willReturn(true);
        $this->payment->method('getLastTransId')
            ->willReturn($lastTransId);

        $this->payment->method('getId')
            ->willReturn(1);

        $this->buildSearchCriteria();

        $this->transactionRepository->method('getTotalCount')
            ->willReturn(0);

        // authorization transaction was expired
        $collection = $this->getNotExpiredExpectedCollection($lastTransId);
        $collection->method('maximumCount')
            ->willReturn(1);

        $this->commandPool->method('get')
            ->with(CaptureStrategyCommand::VAULT_CAPTURE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    public function testVaultCaptureExecute()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentData;

        $this->subjectReader->method('readPayment')
            ->with($subject)
            ->willReturn($paymentData);

        $this->payment->method('getAuthorizationTransaction')
            ->willReturn(true);

        $this->payment->method('getId')
            ->willReturn(1);

        $this->buildSearchCriteria();

        $this->transactionRepository->method('getTotalCount')
            ->willReturn(1);

        $this->commandPool->method('get')
            ->with(CaptureStrategyCommand::VAULT_CAPTURE)
            ->willReturn($this->command);

        $this->strategyCommand->execute($subject);
    }

    /**
     * Creates mock for payment data object and order payment
     * @return MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment', 'getOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getPayment')
            ->willReturn($this->payment);

        $order = $this->getMockBuilder(OrderAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->method('getOrder')
            ->willReturn($order);

        return $mock;
    }

    /**
     * Creates mock for gateway command object
     */
    private function initCommandMock()
    {
        $this->command = $this->getMockBuilder(GatewayCommand::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();

        $this->command->method('execute')
            ->willReturn([]);
    }

    /**
     * Creates mock for filter object
     */
    private function initFilterBuilderMock()
    {
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setField', 'setValue', 'create', '__wakeup'])
            ->getMock();
    }

    /**
     * Builds search criteria
     */
    private function buildSearchCriteria()
    {
        $this->filterBuilder->expects(self::exactly(2))
            ->method('setField')
            ->willReturnSelf();
        $this->filterBuilder->expects(self::exactly(2))
            ->method('setValue')
            ->willReturnSelf();

        $searchCriteria = new SearchCriteria();
        $this->searchCriteriaBuilder->expects(self::exactly(2))
            ->method('addFilters')
            ->willReturnSelf();
        $this->searchCriteriaBuilder->method('create')
            ->willReturn($searchCriteria);

        $this->transactionRepository->method('getList')
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
