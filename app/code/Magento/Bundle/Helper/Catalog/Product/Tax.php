<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Helper\Catalog\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;

class Tax extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TaxClassKeyInterfaceFactory
     */
    protected $taxClassKeyFactory;

    /**
     * @var Config
     */
    protected $taxConfig;

    /**
     * @var QuoteDetailsInterfaceFactory
     */
    protected $quoteDetailsFactory;

    /**
     * @var QuoteDetailsItemInterfaceFactory
     */
    protected $quoteDetailsItemFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var TaxCalculationInterface
     */
    protected $taxCalculationService;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var RegionInterfaceFactory
     */
    protected $regionFactory;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param TaxClassKeyInterfaceFactory $taxClassKeyFactory
     * @param Config $taxConfig
     * @param QuoteDetailsInterfaceFactory $quoteDetailsFactory
     * @param QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory
     * @param TaxCalculationInterface $taxCalculationService
     * @param CustomerSession $customerSession
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupRepositoryInterface $customerGroupRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context                          $context,
        StoreManagerInterface            $storeManager,
        TaxClassKeyInterfaceFactory      $taxClassKeyFactory,
        Config                           $taxConfig,
        QuoteDetailsInterfaceFactory     $quoteDetailsFactory,
        QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory,
        TaxCalculationInterface          $taxCalculationService,
        CustomerSession                  $customerSession,
        PriceCurrencyInterface           $priceCurrency,
        GroupRepositoryInterface         $customerGroupRepository
    ) {
        $this->storeManager = $storeManager;
        $this->taxClassKeyFactory = $taxClassKeyFactory;
        $this->taxConfig = $taxConfig;
        $this->quoteDetailsFactory = $quoteDetailsFactory;
        $this->quoteDetailsItemFactory = $quoteDetailsItemFactory;
        $this->taxCalculationService = $taxCalculationService;
        $this->customerSession = $customerSession;
        $this->priceCurrency = $priceCurrency;
        $this->customerGroupRepository = $customerGroupRepository;
        parent::__construct($context);
    }

    /**
     * Get product price with all tax settings processing for cart
     *
     * @param Product $product
     * @param float $price
     * @param bool|null $includingTax
     * @param AbstractAddress|null $shippingAddress
     * @param AbstractAddress|null $billingAddress
     * @param int|null $ctc
     * @param Store|bool|int|string|null $store
     * @param bool|null $priceIncludesTax
     * @param bool $roundPrice
     * @return float
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTaxPrice(
        Product               $product,
        float                 $price,
        bool                  $includingTax = null,
        AbstractAddress       $shippingAddress = null,
        AbstractAddress       $billingAddress = null,
        int                   $ctc = null,
        Store|bool|int|string $store = null,
        bool                  $priceIncludesTax = null,
        bool                  $roundPrice = true
    ): float {
        if (!$price) {
            return $price;
        }

        $store = $this->storeManager->getStore($store);
        if ($priceIncludesTax === null) {
            $priceIncludesTax = $this->taxConfig->priceIncludesTax($store);
        }

        $shippingAddressDataObject = null;
        if ($shippingAddress === null) {
            $shippingAddressDataObject =
                $this->convertDefaultTaxAddress($this->customerSession->getDefaultTaxShippingAddress());
        } elseif ($shippingAddress instanceof AbstractAddress) {
            $shippingAddressDataObject = $shippingAddress->getDataModel();
        }

        $billingAddressDataObject = null;
        if ($billingAddress === null) {
            $billingAddressDataObject =
                $this->convertDefaultTaxAddress($this->customerSession->getDefaultTaxBillingAddress());
        } elseif ($billingAddress instanceof AbstractAddress) {
            $billingAddressDataObject = $billingAddress->getDataModel();
        }

        $taxClassKey = $this->taxClassKeyFactory->create();
        $taxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
            ->setValue($product->getTaxClassId());

        if ($ctc === null && $this->customerSession->getCustomerGroupId() != null) {
            $ctc = $this->customerGroupRepository->getById($this->customerSession->getCustomerGroupId())
                ->getTaxClassId();
        }

        $customerTaxClassKey = $this->taxClassKeyFactory->create();
        $customerTaxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
            ->setValue($ctc);

        $item = $this->quoteDetailsItemFactory->create();
        $item->setQuantity(1)
            ->setCode($product->getSku())
            ->setShortDescription($product->getShortDescription())
            ->setTaxClassKey($taxClassKey)
            ->setIsTaxIncluded($priceIncludesTax)
            ->setType('product')
            ->setUnitPrice($price);

        $quoteDetails = $this->quoteDetailsFactory->create();
        $quoteDetails->setShippingAddress($shippingAddressDataObject)
            ->setBillingAddress($billingAddressDataObject)
            ->setCustomerTaxClassKey($customerTaxClassKey)
            ->setItems([$item])
            ->setCustomerId($this->customerSession->getCustomerId());

        $storeId = null;
        $storeId = $store?->getId();
        $taxDetails = $this->taxCalculationService->calculateTax($quoteDetails, $storeId, $roundPrice);
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

        if ($roundPrice) {
            return $this->priceCurrency->round($price);
        } else {
            return $price;
        }
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

    /**
     * Convert tax address array to address data object with country id and postcode
     *
     * @param array|null $taxAddress
     * @return AddressInterface|null
     */
    private function convertDefaultTaxAddress(array $taxAddress = null)
    {
        if (empty($taxAddress)) {
            return null;
        }
        /** @var AddressInterface $addressDataObject */
        $addressDataObject = $this->addressFactory->create()
            ->setCountryId($taxAddress['country_id'])
            ->setPostcode($taxAddress['postcode']);

        if (isset($taxAddress['region_id'])) {
            $addressDataObject->setRegion($this->regionFactory->create()->setRegionId($taxAddress['region_id']));
        }
        return $addressDataObject;
    }
}
