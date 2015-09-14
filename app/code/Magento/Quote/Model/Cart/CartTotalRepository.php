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
use Magento\Quote\Api\CouponManagementInterface;

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
    private $itemConverter;

    /**
     * @var CouponManagementInterface
     */
    protected $couponService;

    /**
     * @var TotalsConverter
     */
    protected $totalsConverter;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsReader
     */
    protected $reader;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @param Api\Data\TotalsInterfaceFactory $totalsFactory
     * @param QuoteRepository $quoteRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param CouponManagementInterface $couponService
     * @param TotalsConverter $totalsConverter
     * @param ItemConverter $converter
     * @param \Magento\Quote\Model\Quote\TotalsReader $reader
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     */
    public function __construct(
        Api\Data\TotalsInterfaceFactory $totalsFactory,
        QuoteRepository $quoteRepository,
        DataObjectHelper $dataObjectHelper,
        CouponManagementInterface $couponService,
        TotalsConverter $totalsConverter,
        ItemConverter $converter,
        \Magento\Quote\Model\Quote\TotalsReader $reader,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
    ) {
        $this->totalsFactory = $totalsFactory;
        $this->quoteRepository = $quoteRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->couponService = $couponService;
        $this->totalsConverter = $totalsConverter;
        $this->itemConverter = $converter;
        $this->reader = $reader;
        $this->totalsCollector = $totalsCollector;
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
//        $shippingAddress = $quote->getShippingAddress();
//        if ($quote->isVirtual()) {
//            $totalsData = array_merge($quote->getBillingAddress()->getData(), $quote->getData());
//        } else {
//            $totalsData = array_merge($shippingAddress->getData(), $quote->getData());
//        }

        $total = $this->totalsCollector->collectQuoteTotals($quote);
        /** @var \Magento\Quote\Api\Data\TotalsInterface $totals */
        $totals = $this->totalsFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $totals,
            $total->getData(),
            '\Magento\Quote\Api\Data\TotalsInterface'
        );
        $items = [];
        foreach ($quote->getAllVisibleItems() as $index => $item) {
            $items[$index] = $this->itemConverter->modelToDataObject($item);
        }
        $totals->setCouponCode($this->couponService->get($cartId));
        $calculatedTotals = $this->totalsConverter->process($this->reader->fetch($total, $quote->getStoreId()));
        $amount = $totals->getGrandTotal() - $totals->getTaxAmount();
        $amount = $amount > 0 ? $amount : 0;
        $totals->setGrandTotal($amount);
        $totals->setTotalSegments($calculatedTotals);
        $totals->setItems($items);
        $totals->setItemsQty($quote->getItemsQty());
        return $totals;
    }
}
