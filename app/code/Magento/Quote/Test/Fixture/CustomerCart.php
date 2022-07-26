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
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class CustomerCart implements RevertibleDataFixtureInterface
{

    private const DEFAULT_DATA = [
        'customer_id' => null
    ];

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
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var DataMerger
     */
    private $dataMerger;

    /**
     * @param ServiceFactory $serviceFactory
     * @param CartRepositoryInterface $cartRepository
     * @param CartManagementInterface $cartManagement
     * @param QuoteResource $quoteResource
     * @param QuoteFactory $quoteFactory
     * @param ProcessorInterface $dataProcessor
     * @param DataMerger $dataMerger
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        QuoteResource $quoteResource,
        QuoteFactory $quoteFactory,
        ProcessorInterface $dataProcessor,
        DataMerger $dataMerger,
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->quoteResource = $quoteResource;
        $this->quoteFactory = $quoteFactory;
        $this->dataProcessor = $dataProcessor;
        $this->dataMerger = $dataMerger;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->prepareData($data);
        $customerId = $data['customer_id'] ?? null;
        $storeId = $data['store_id'] ?? null;
        if ($storeId) {
            $cartService = $this->serviceFactory->create(CartManagementInterface::class, 'createEmptyCart');
        } else {
            $cartService = $this->serviceFactory->create(CartManagementInterface::class, 'createEmptyCartForCustomer');
        }
        $cartId = $cartService->execute(['customerId' => $customerId]);
        if ($storeId) {
            $cartService = $this->serviceFactory->create(CartManagementInterface::class, 'assignCustomer');
            $cartService->execute(['cartId' => $cartId, 'customerId' => $customerId, 'storeId' => $storeId]);
        }
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

    /**
     * Prepare quote data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data, false);
        return $this->dataProcessor->process($this, $data);
    }
}
