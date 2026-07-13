<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\CustomerGraphQl\Model\GetGuestOrdersByEmail;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetGuestOrdersByEmailTest extends TestCase
{
    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var GetGuestOrdersByEmail
     */
    private GetGuestOrdersByEmail $getGuestOrdersByEmail;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->getGuestOrdersByEmail = new GetGuestOrdersByEmail(
            $this->orderRepository,
            $this->scopeConfig,
            $this->searchCriteriaBuilder
        );

        parent::setUp();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testExecute(): void
    {
        $email = 'customer@email.com';
        $storeId = 1;
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())->method('getEmail')->willReturn($email);
        $customer->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $this->searchCriteriaBuilder->expects($this->exactly(3))
            ->method('addFilter')
            ->willReturnSelf();
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                Share::XML_PATH_CUSTOMER_ACCOUNT_SHARE,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            )
            ->willReturn(1);
        $this->orderRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($this->createMock(OrderSearchResultInterface::class));
        $this->getGuestOrdersByEmail->execute($customer);
    }
}
