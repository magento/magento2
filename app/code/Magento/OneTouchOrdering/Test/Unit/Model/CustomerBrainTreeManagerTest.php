<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\OneTouchOrdering\Model\CustomerBrainTreeManager;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\TestCase;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;

class CustomerBrainTreeManagerTest extends TestCase
{
    /**
     * @var FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilder;
    /**
     * @var  PaymentTokenRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentTokenRepository;
    /**
     * @var  SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilder;
    /**
     * @var  CustomerBrainTreeManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerBrainTreeManager;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setValue', 'setField', 'create', 'setConditionType'])
            ->getMock();
        $this->filterBuilder->method('setValue')->willReturnSelf();
        $this->filterBuilder->method('setField')->willReturnSelf();
        $this->filterBuilder->method('setConditionType')->willReturnSelf();

        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $this->paymentTokenRepository = $this->createMock(PaymentTokenRepositoryInterface::class);

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilters', 'create'])
            ->getMock();
        $this->searchCriteriaBuilder->method('addFilters')->willReturn($this->searchCriteriaBuilder);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $dateTimeFactory = new DateTimeFactory();

        $this->customerBrainTreeManager = $objectManager->getObject(
            CustomerBrainTreeManager::class,
            [
                'repository' => $this->paymentTokenRepository,
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'dateTimeFactory' => $dateTimeFactory
            ]
        );
    }

    public function testGetVisibleAvailableTokens()
    {
        $customerId = 21;

        $this->filterBuilder->method('setField')->withConsecutive($this->logicalOr(
            [PaymentTokenInterface::CUSTOMER_ID],
            [PaymentTokenInterface::IS_VISIBLE],
            [PaymentTokenInterface::IS_ACTIVE],
            [PaymentTokenInterface::PAYMENT_METHOD_CODE],
            [PaymentTokenInterface::EXPIRES_AT]
        ));

        $this->filterBuilder->method('setValue')->withConsecutive(
            [$customerId],
            [1],
            [1],
            [BrainTreeConfigProvider::CODE],
            [$this->anything()]
        );
        
        $this->filterBuilder->method('create')
            ->willReturnOnConsecutiveCalls('filter1', 'filter2', 'filter3', 'filter4', 'filter5');

        $this->searchCriteriaBuilder->method('addFilters')->withConsecutive(
            [['filter1']],
            [['filter2']],
            [['filter3']],
            [['filter4']],
            [['filter5']]
        );

        $paymentTokenSearchResult = $this->getMockForAbstractClass(
            PaymentTokenSearchResultsInterface::class
        );
        $paymentTokenSearchResult->method('getItems')->willReturn([true]);
        $this->paymentTokenRepository->method('getList')->willReturn($paymentTokenSearchResult);

        $result = $this->customerBrainTreeManager->getCustomerBrainTreeCard($customerId);
        $this->assertTrue($result);
    }
}
