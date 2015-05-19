<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Quote\Model\Cart\Totals\ItemConverter;

/**
 * Cart totals data object.
 */
class CartTotalRepository implements CartTotalRepositoryInterface
{
    /**
     * Cart totals factory.
     *
     * @var Api\Data\TotalsInterfaceFactory
     */
    private $totalsFactory;

    /**
     * Quote repository.
     *
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ConfigurationPool
     */
    private $converter;

    /**
     * @param Api\Data\TotalsInterfaceFactory $totalsFactory
     * @param QuoteRepository $quoteRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param ItemConverter $converter
     */
    public function __construct(
        Api\Data\TotalsInterfaceFactory $totalsFactory,
        QuoteRepository $quoteRepository,
        DataObjectHelper $dataObjectHelper,
        ItemConverter $converter
    ) {
        $this->totalsFactory = $totalsFactory;
        $this->quoteRepository = $quoteRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->converter = $converter;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The cart ID.
     * @return Totals Quote totals data.
     */
    public function get($cartId)
    {
        /**
         * Quote.
         *
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();
        if ($quote->isVirtual()) {
            $totalsData = array_merge($quote->getBillingAddress()->getData(), $quote->getData());
        } else {
            $totalsData = array_merge($shippingAddress->getData(), $quote->getData());
        }
        $totals = $this->totalsFactory->create();
        $this->dataObjectHelper->populateWithArray($totals, $totalsData, '\Magento\Quote\Api\Data\TotalsInterface');
        $items = [];
        foreach ($quote->getAllVisibleItems() as $index => $item) {
            $items[$index] = $this->converter->modelToDataObject($item);
        }
        $totals->setItems($items);

        return $totals;
    }
}
