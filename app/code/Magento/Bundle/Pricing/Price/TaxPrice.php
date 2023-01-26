<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class TaxPrice
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TaxClassKeyInterfaceFactory
     */
    private $taxClassKeyFactory;

    /**
     * @var Config
     */
    private $taxConfig;

    /**
     * @var QuoteDetailsInterfaceFactory
     */
    private $quoteDetailsFactory;

    /**
     * @var QuoteDetailsItemInterfaceFactory
     */
    private $quoteDetailsItemFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var TaxCalculationInterface
     */
    private $taxCalculationService;

    /**
     * @var GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param StoreManagerInterface $storeManager
     * @param TaxClassKeyInterfaceFactory $taxClassKeyFactory
     * @param Config $taxConfig
     * @param QuoteDetailsInterfaceFactory $quoteDetailsFactory
     * @param QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory
     * @param TaxCalculationInterface $taxCalculationService
     * @param CustomerSession $customerSession
     * @param GroupRepositoryInterface $customerGroupRepository
     * @param Session $checkoutSession
     */
    public function __construct(
        StoreManagerInterface            $storeManager,
        TaxClassKeyInterfaceFactory      $taxClassKeyFactory,
        Config                           $taxConfig,
        QuoteDetailsInterfaceFactory     $quoteDetailsFactory,
        QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory,
        TaxCalculationInterface          $taxCalculationService,
        CustomerSession                  $customerSession,
        GroupRepositoryInterface         $customerGroupRepository,
        Session                          $checkoutSession
    ) {
        $this->storeManager = $storeManager;
        $this->taxClassKeyFactory = $taxClassKeyFactory;
        $this->taxConfig = $taxConfig;
        $this->quoteDetailsFactory = $quoteDetailsFactory;
        $this->quoteDetailsItemFactory = $quoteDetailsItemFactory;
        $this->taxCalculationService = $taxCalculationService;
        $this->customerSession = $customerSession;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get product price with all tax settings processing for cart
     *
     * @param Product $product
     * @param float $price
     * @param bool|null $includingTax
     * @param int|null $ctc
     * @param Store|bool|int|string|null $store
     * @param bool|null $priceIncludesTax
     * @return float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getTaxPrice(
        Product               $product,
        float                 $price,
        bool                  $includingTax = null,
        int                   $ctc = null,
        Store|bool|int|string $store = null,
        bool                  $priceIncludesTax = null
    ): float {
        if (!$price) {
            return $price;
        }

        $store = $this->storeManager->getStore($store);
        $storeId = $store?->getId();
        $taxClassKey = $this->taxClassKeyFactory->create();
        $customerTaxClassKey = $this->taxClassKeyFactory->create();
        $item = $this->quoteDetailsItemFactory->create();
        $quoteDetails = $this->quoteDetailsFactory->create();
        $customerQuote = $this->checkoutSession->getQuote();

        if ($priceIncludesTax === null) {
            $priceIncludesTax = $this->taxConfig->priceIncludesTax($store);
        }

        $taxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
            ->setValue($product->getTaxClassId());

        if ($ctc === null && $this->customerSession->getCustomerGroupId() != null) {
            $ctc = $this->customerGroupRepository->getById($this->customerSession->getCustomerGroupId())
                ->getTaxClassId();
        }

        $customerTaxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
            ->setValue($ctc);

        $item->setQuantity(1)
            ->setCode($product->getSku())
            ->setShortDescription($product->getShortDescription())
            ->setTaxClassKey($taxClassKey)
            ->setIsTaxIncluded($priceIncludesTax)
            ->setType('product')
            ->setUnitPrice($price);

        $quoteDetails
            ->setShippingAddress($customerQuote->getShippingAddress()->getDataModel())
            ->setCustomerTaxClassKey($customerTaxClassKey)
            ->setItems([$item])
            ->setCustomerId($this->customerSession->getCustomerId());

        $taxDetails = $this->taxCalculationService->calculateTax($quoteDetails, $storeId);
        $items = $taxDetails->getItems();
        $taxDetailsItem = array_shift($items);

        if ($includingTax !== null) {
            if ($includingTax) {
                $price = $taxDetailsItem->getPriceInclTax();
            } else {
                $price = $taxDetailsItem->getPrice();
            }
        } else {
            $price = $this->taxConfig->displayCartPricesExclTax($store) ||
            $this->taxConfig->displayCartPricesBoth($store) ?
                $taxDetailsItem->getPrice() : $taxDetailsItem->getPriceInclTax();
        }

        return $price;
    }

    /**
     * Check if both cart prices are shown
     *
     * @param StoreInterface|null $store
     * @return bool
     */
    public function displayCartPricesBoth(StoreInterface $store = null): bool
    {
        return $this->taxConfig->displayCartPricesBoth($store);
    }
}
