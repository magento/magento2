<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OneTouchOrdering\Model\CustomerCreditCardManager;
use PHPUnit\Framework\TestCase;

class CustomerCreditCardManagerTest extends TestCase
{
    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;
    /**
     * @var  \Magento\Vault\Api\PaymentTokenRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenRepository;
    /**
     * @var  \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;
    /**
     * @var  CustomerCreditCardManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCreditCardManager;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->filterBuilder = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setValue', 'setField', 'create', 'setConditionType'])
            ->getMock();
        $this->filterBuilder->method('setValue')->willReturnSelf();
        $this->filterBuilder->method('setField')->willReturnSelf();
        $this->filterBuilder->method('setConditionType')->willReturnSelf();

        $searchCriteria = $this->createMock(
            \Magento\Framework\Api\SearchCriteriaInterface::class
        );
        $this->paymentTokenRepository = $this->createMock(
            \Magento\Vault\Api\PaymentTokenRepositoryInterface::class
        );

        $this->searchCriteriaBuilder = $this->getMockBuilder(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        )->disableOriginalConstructor()
            ->setMethods(['addFilters', 'create'])
            ->getMock();
        $this->searchCriteriaBuilder->method('addFilters')->willReturn($this->searchCriteriaBuilder);
        $this->searchCriteriaBuilder->method('create')->willReturn($searchCriteria);
        $dateTimeFactory = new DateTimeFactory();

        $this->customerCreditCardManager = $objectManager->getObject(
            CustomerCreditCardManager::class,
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
        $ccId = 2;
        $this->filterBuilder->method('setField')->withConsecutive($this->logicalOr(
            [\Magento\Vault\Api\Data\PaymentTokenInterface::CUSTOMER_ID],
            [\Magento\Vault\Api\Data\PaymentTokenInterface::IS_VISIBLE],
            [\Magento\Vault\Api\Data\PaymentTokenInterface::IS_ACTIVE],
            [\Magento\Vault\Api\Data\PaymentTokenInterface::PAYMENT_METHOD_CODE],
            [\Magento\Vault\Api\Data\PaymentTokenInterface::EXPIRES_AT]
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
            \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface::class
        );
        $paymentTokenSearchResult->method('getItems')->willReturn([$ccId => true]);
        $this->paymentTokenRepository->method('getList')->willReturn($paymentTokenSearchResult);

        $result = $this->customerCreditCardManager->getCustomerCreditCard($customerId, $ccId);
        $this->assertTrue($result);
    }

    public function testGetCustomerCreditCardNoCC()
    {
        $customerId = 21;

        $paymentTokenSearchResult = $this->getMockForAbstractClass(
            \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface::class
        );
        $paymentTokenSearchResult->method('getItems')->willReturn([]);
        $this->paymentTokenRepository->method('getList')->willReturn($paymentTokenSearchResult);
        $this->expectException(LocalizedException::class);

        $this->customerCreditCardManager->getCustomerCreditCard($customerId, 2);
    }

    public function testGetCustomerCreditCardNoRequestedCC()
    {
        $customerId = 21;

        $paymentTokenSearchResult = $this->getMockForAbstractClass(
            \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface::class
        );
        $paymentTokenSearchResult->method('getItems')->willReturn([3 => true]);
        $this->paymentTokenRepository->method('getList')->willReturn($paymentTokenSearchResult);
        $this->expectException(LocalizedException::class);

        $this->customerCreditCardManager->getCustomerCreditCard($customerId, 2);
    }
}
