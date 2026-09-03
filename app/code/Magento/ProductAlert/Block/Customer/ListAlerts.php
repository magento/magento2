<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Block\Customer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\ProductAlert\Helper\Data as ProductAlertHelper;
use Magento\ProductAlert\Model\Price as PriceAlert;
use Magento\ProductAlert\Model\ResourceModel\Price\Collection as PriceAlertCollection;
use Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory as PriceAlertCollectionFactory;
use Magento\ProductAlert\Model\ResourceModel\Stock\Collection as StockAlertCollection;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory as StockAlertCollectionFactory;
use Magento\ProductAlert\Model\Stock as StockAlert;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Pager;

/**
 * Customer product alerts list block.
 */
class ListAlerts extends Template
{
    /**
     * @var PriceAlertCollection|null
     */
    private $priceAlerts;

    /**
     * @var StockAlertCollection|null
     */
    private $stockAlerts;

    /**
     * @var Product[]|null
     */
    private $products;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param PriceAlertCollectionFactory $priceAlertCollectionFactory
     * @param StockAlertCollectionFactory $stockAlertCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductAlertHelper $productAlertHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        private CustomerSession $customerSession,
        private PriceAlertCollectionFactory $priceAlertCollectionFactory,
        private StockAlertCollectionFactory $stockAlertCollectionFactory,
        private ProductCollectionFactory $productCollectionFactory,
        private StoreManagerInterface $storeManager,
        private ProductAlertHelper $productAlertHelper,
        private PriceCurrencyInterface $priceCurrency,
        private PostHelper $postDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Initialize collection pagers.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->isPriceAlertAllowed() && $this->getPriceAlerts()->getSize()) {
            $pricePager = $this->getLayout()->createBlock(
                Pager::class,
                'product.alert.price.list.pager'
            );
            $pricePager->setPageVarName('price_p')
                ->setLimitVarName('price_limit')
                ->setCollection($this->getPriceAlerts());
            $this->setChild('price_pager', $pricePager);
        }

        if ($this->isStockAlertAllowed() && $this->getStockAlerts()->getSize()) {
            $stockPager = $this->getLayout()->createBlock(
                Pager::class,
                'product.alert.stock.list.pager'
            );
            $stockPager->setPageVarName('stock_p')
                ->setLimitVarName('stock_limit')
                ->setCollection($this->getStockAlerts());
            $this->setChild('stock_pager', $stockPager);
        }

        return parent::_prepareLayout();
    }

    /**
     * HTML for the price alerts pager.
     *
     * @return string
     */
    public function getPricePagerHtml(): string
    {
        return $this->getChildHtml('price_pager');
    }

    /**
     * HTML for the stock alerts pager.
     *
     * @return string
     */
    public function getStockPagerHtml(): string
    {
        return $this->getChildHtml('stock_pager');
    }

    /**
     * Whether price alerts are enabled in config.
     *
     * @return bool
     */
    public function isPriceAlertAllowed(): bool
    {
        return $this->productAlertHelper->isPriceAlertAllowed();
    }

    /**
     * Whether stock alerts are enabled in config.
     *
     * @return bool
     */
    public function isStockAlertAllowed(): bool
    {
        return $this->productAlertHelper->isStockAlertAllowed();
    }

    /**
     * Get price alert subscriptions for the current customer and store.
     *
     * @return PriceAlertCollection
     * @throws NoSuchEntityException
     */
    public function getPriceAlerts(): PriceAlertCollection
    {
        if ($this->priceAlerts === null) {
            $store = $this->storeManager->getStore();
            $this->priceAlerts = $this->priceAlertCollectionFactory->create();
            $this->priceAlerts->addFieldToFilter('customer_id', (int)$this->customerSession->getCustomerId())
                ->addWebsiteFilter($store->getWebsiteId())
                ->addFieldToFilter('store_id', (int)$store->getId())
                ->setOrder('add_date', 'DESC');
        }
        return $this->priceAlerts;
    }

    /**
     * Get stock alert subscriptions for the current customer and store.
     *
     * @return StockAlertCollection
     * @throws NoSuchEntityException
     */
    public function getStockAlerts(): StockAlertCollection
    {
        if ($this->stockAlerts === null) {
            $store = $this->storeManager->getStore();
            $this->stockAlerts = $this->stockAlertCollectionFactory->create();
            $this->stockAlerts->addFieldToFilter('customer_id', (int)$this->customerSession->getCustomerId())
                ->addWebsiteFilter($store->getWebsiteId())
                ->addFieldToFilter('store_id', (int)$store->getId())
                ->setOrder('add_date', 'DESC');
        }
        return $this->stockAlerts;
    }

    /**
     * Whether the customer has price alert subscriptions.
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function hasPriceAlerts(): bool
    {
        return $this->isPriceAlertAllowed() && $this->getPriceAlerts()->getSize() > 0;
    }

    /**
     * Whether the customer has stock alert subscriptions.
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function hasStockAlerts(): bool
    {
        return $this->isStockAlertAllowed() && $this->getStockAlerts()->getSize() > 0;
    }

    /**
     * Get product model for an alert row.
     *
     * @param PriceAlert|StockAlert $alert
     * @return ProductInterface|Product|null
     * @throws NoSuchEntityException
     */
    public function getProduct($alert)
    {
        $this->loadProducts();
        $productId = (int)$alert->getProductId();
        return $this->products[$productId] ?? null;
    }

    /**
     * Format subscription date for display.
     *
     * @param string $date
     * @return string
     */
    public function formatAlertDate(string $date): string
    {
        return $this->formatDate($date, \IntlDateFormatter::SHORT);
    }

    /**
     * Format stored alert price for display.
     *
     * @param float|string $price
     * @return string
     */
    public function formatAlertPrice($price): string
    {
        return $this->priceCurrency->format((float)$price, false);
    }

    /**
     * URL to unsubscribe from a single price alert.
     *
     * @param int $productId
     * @return string
     */
    public function getUnsubscribePriceUrl(int $productId): string
    {
        return $this->getUrl('productalert/unsubscribe/price', ['product' => $productId]);
    }

    /**
     * data-post payload for single stock unsubscribe.
     *
     * @param int $productId
     * @return string
     */
    public function getUnsubscribeStockPostData(int $productId): string
    {
        return $this->postDataHelper->getPostData(
            $this->getUrl('productalert/unsubscribe/stock'),
            ['product' => $productId]
        );
    }

    /**
     * URL to unsubscribe from all price alerts.
     *
     * @return string
     */
    public function getUnsubscribeAllPriceUrl(): string
    {
        return $this->getUrl('productalert/unsubscribe/priceAll');
    }

    /**
     * URL to unsubscribe from all stock alerts.
     *
     * @return string
     */
    public function getUnsubscribeAllStockUrl(): string
    {
        return $this->getUrl('productalert/unsubscribe/stockAll');
    }

    /**
     * Back URL to customer account.
     *
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Load products for current page alert rows once.
     *
     * @return void
     * @throws NoSuchEntityException
     */
    private function loadProducts(): void
    {
        if ($this->products !== null) {
            return;
        }

        $productIds = [];
        if ($this->isPriceAlertAllowed()) {
            foreach ($this->getPriceAlerts() as $alert) {
                $productIds[] = (int)$alert->getProductId();
            }
        }
        if ($this->isStockAlertAllowed()) {
            foreach ($this->getStockAlerts() as $alert) {
                $productIds[] = (int)$alert->getProductId();
            }
        }
        $productIds = array_values(array_unique(array_filter($productIds)));

        $this->products = [];
        if (!$productIds) {
            return;
        }

        /** @var ProductCollection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setFlag('has_stock_status_filter', true);
        $collection->addAttributeToSelect(['name', 'price', 'status', 'visibility'])
            ->addIdFilter($productIds)
            ->addStoreFilter($this->storeManager->getStore()->getId());

        foreach ($collection as $product) {
            $this->products[(int)$product->getId()] = $product;
        }
    }
}
