<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\QuoteFactory;

/**
 * Get quote item id by reserved order id and product sku
 */
class GetQuoteItemIdByReservedQuoteIdAndSku
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param QuoteFactory $quoteFactory
     * @param QuoteResource $quoteResource
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteResource $quoteResource,
        ProductRepositoryInterface $productRepository
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteResource = $quoteResource;
        $this->productRepository = $productRepository;
    }

    /**
     * Get quote item id by reserved order id and product sku
     *
     * @param string $reservedOrderId
     * @param string $sku
     * @return int
     * @throws NoSuchEntityException
     */
    public function execute(string $reservedOrderId, string $sku): int
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $product = $this->productRepository->get($sku);

        return (int)$quote->getItemByProduct($product)->getId();
    }
}
