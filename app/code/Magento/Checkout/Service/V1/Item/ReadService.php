<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Item;

/**
 * Read service object.
 */
class ReadService implements ReadServiceInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Item mapper.
     *
     * @var \Magento\Checkout\Service\V1\Data\Cart\ItemMapper
     */
    protected $itemMapper;

    /**
     * Constructs a read service object.
     *
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository Quote repository.
     * @param \Magento\Checkout\Service\V1\Data\Cart\ItemMapper $itemMapper Item mapper.
     */
    public function __construct(
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        \Magento\Checkout\Service\V1\Data\Cart\ItemMapper $itemMapper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->itemMapper = $itemMapper;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Checkout\Service\V1\Data\Cart\Item[] Array of items.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function getList($cartId)
    {
        $output = [];
        /** @var  \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        /** @var  \Magento\Sales\Model\Quote\Item  $item */
        foreach ($quote->getAllItems() as $item) {
            $output[] = $this->itemMapper->extractDto($item);
        }
        return $output;
    }
}
