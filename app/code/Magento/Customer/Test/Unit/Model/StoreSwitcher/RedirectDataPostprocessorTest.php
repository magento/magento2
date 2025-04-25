<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\StoreSwitcher;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\StoreSwitcher\RedirectDataPostprocessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RedirectDataPostprocessorTest extends TestCase
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var RedirectDataPostprocessor
     */
    private $model;
    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $customerRegistry = $this->createMock(CustomerRegistry::class);
        $this->session = $this->createMock(Session::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->model = new RedirectDataPostprocessor(
            $customerRegistry,
            $this->session,
            $logger
        );

        $store1 = $this->createConfiguredMock(
            StoreInterface::class,
            [
                'getCode' => 'en',
                'getId' => 1,
            ]
        );
        $store2 = $this->createConfiguredMock(
            StoreInterface::class,
            [
                'getCode' => 'fr',
                'getId' => 2,
            ]
        );
        $this->context = $this->createConfiguredMock(
            ContextInterface::class,
            [
                'getFromStore' => $store2,
                'getTargetStore' => $store1,
            ]
        );

        $customerRegistry->method('retrieve')
            ->willReturnCallback(
                function ($id) {
                    switch ($id) {
                        case 1:
                            throw new NoSuchEntityException(__('Customer does not exist'));
                        case 2:
                            throw new LocalizedException(__('Something went wrong'));
                        default:
                            $customer = $this->createMock(Customer::class);
                            $customer->method('getSharedStoreIds')
                                ->willReturn(!($id % 2) ? [1, 2] : [2]);
                            $customer->method('getDataModel')
                                ->willReturn(
                                    $this->createConfiguredMock(
                                        CustomerInterface::class,
                                        [
                                            'getId' => $id
                                        ]
                                    )
                                );
                            return $customer;
                    }
                }
            );
    }

    public function testProcessShouldLoginCustomerIfCustomerIsRegisteredInTargetStore(): void
    {
        $data = ['customer_id' => 4];
        $this->session->expects($this->once())
            ->method('setCustomerDataAsLoggedIn');
        $this->model->process($this->context, $data);
    }

    public function testProcessShouldNotLoginCustomerIfNotRegisteredInTargetStore(): void
    {
        $data = ['customer_id' => 3];
        $this->session->expects($this->never())
            ->method('setCustomerDataAsLoggedIn');
        $this->model->process($this->context, $data);
    }

    public function testProcessShouldThrowExceptionIfCustomerDoesNotExist(): void
    {
        $this->expectExceptionMessage('Something went wrong.');
        $data = ['customer_id' => 1];
        $this->session->expects($this->never())
            ->method('setCustomerDataAsLoggedIn');
        $this->model->process($this->context, $data);
    }

    public function testProcessShouldThrowExceptionIfAnErrorOccur(): void
    {
        $this->expectExceptionMessage('Something went wrong.');
        $data = ['customer_id' => 2];
        $this->session->expects($this->never())
            ->method('setCustomerDataAsLoggedIn');
        $this->model->process($this->context, $data);
    }
}
