<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class CustomerCart implements RevertibleDataFixtureInterface
{

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @param ServiceFactory $serviceFactory
     * @param CartRepositoryInterface $cartRepository
     * @param CartManagementInterface $cartManagement
     * @param QuoteResource $quoteResource
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        QuoteResource $quoteResource,
        QuoteFactory $quoteFactory
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->quoteResource = $quoteResource;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $customerId = $data['customer_id'] ?? null;
        $cartService = $this->serviceFactory->create(CartManagementInterface::class, 'createEmptyCartForCustomer');
        $cartId = $cartService->execute(['customerId' => $customerId]);
        $cartRepositoryService = $this->serviceFactory->create(CartRepositoryInterface::class, 'get');
        return $cartRepositoryService->execute(['cartId' => $cartId]);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $cartRepositoryService = $this->serviceFactory->create(CartRepositoryInterface::class, 'delete');
        $cartRepositoryService->execute(['quote' => $data]);
    }
}
