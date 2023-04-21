<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\StoreSwitcher;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\StoreSwitcher\RedirectDataPreprocessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RedirectDataPreprocessorTest extends TestCase
{
    /**
     * @var RedirectDataPreprocessor
     */
    private $model;
    /**
     * @var Session|MockObject
     */
    private $context;
    /**
     * @var UserContextInterface|MockObject
     */
    private $session;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $customerRegistry = $this->createMock(CustomerRegistry::class);
        $logger = $this->createMock(LoggerInterface::class);
        $this->session = $this->createMock(Session::class);
        $this->model = new RedirectDataPreprocessor(
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
                            $customer->method('getId')
                                ->willReturn($id);
                            return $customer;
                    }
                }
            );
    }

    /**
     * @dataProvider processDataProvider
     * @param int|null $customerId
     * @param array $data
     */
    public function testProcess(?int $customerId, array $data): void
    {
        $this->session->method('isLoggedIn')
            ->willReturn(true);
        $this->session->method('getCustomerId')
            ->willReturn($customerId);
        $this->assertEquals($data, $this->model->process($this->context, []));
    }

    /**
     * @return array
     */
    public function processDataProvider(): array
    {
        return [
            [1, []],
            [2, []],
            [3, []],
            [4, ['customer_id' => 4]]
        ];
    }
}
