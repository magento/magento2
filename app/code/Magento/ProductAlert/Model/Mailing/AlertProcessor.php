<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model\Mailing;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime;
use Magento\ProductAlert\Model\Email;
use Magento\ProductAlert\Model\EmailFactory;
use Magento\ProductAlert\Model\Price;
use Magento\ProductAlert\Model\ProductSalability;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory as StockCollectionFactory;
use Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory as PriceCollectionFactory;
use Magento\ProductAlert\Model\Stock;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

/**
 * Class for mailing Product Alerts
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AlertProcessor
{
    public const ALERT_TYPE_STOCK = 'stock';
    public const ALERT_TYPE_PRICE = 'price';

    /**
     * @var EmailFactory
     */
    private $emailFactory;

    /**
     * @var PriceCollectionFactory
     */
    private $priceCollectionFactory;

    /**
     * @var StockCollectionFactory
     */
    private $stockCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Data
     */
    private $catalogData;

    /**
     * @var ProductSalability
     */
    private $productSalability;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ErrorEmailSender
     */
    private $errorEmailSender;

    /**
     * @param EmailFactory $emailFactory
     * @param PriceCollectionFactory $priceCollectionFactory
     * @param StockCollectionFactory $stockCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Data $catalogData
     * @param ProductSalability $productSalability
     * @param StoreManagerInterface $storeManager
     * @param ErrorEmailSender $errorEmailSender
     */
    public function __construct(
        EmailFactory $emailFactory,
        PriceCollectionFactory $priceCollectionFactory,
        StockCollectionFactory $stockCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        Data $catalogData,
        ProductSalability $productSalability,
        StoreManagerInterface $storeManager,
        ErrorEmailSender $errorEmailSender
    ) {
        $this->emailFactory = $emailFactory;
        $this->priceCollectionFactory = $priceCollectionFactory;
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->catalogData = $catalogData;
        $this->productSalability = $productSalability;
        $this->storeManager = $storeManager;
        $this->errorEmailSender = $errorEmailSender;
    }

    /**
     * Process product alerts
     *
     * @param string $alertType
     * @param array $customerIds
     * @param int $websiteId
     * @throws \Exception
     */
    public function process(string $alertType, array $customerIds, int $websiteId): void
    {
        $this->validateAlertType($alertType);
        $errors = $this->processAlerts($alertType, $customerIds, $websiteId);
        if (!empty($errors)) {
            /** @var Website $website */
            $website = $this->storeManager->getWebsite($websiteId);
            $defaultStoreId = $website->getDefaultStore()->getId();
            $this->errorEmailSender->execute($errors, $defaultStoreId);
        }
    }

    /**
     * Process product alerts
     *
     * @param string $alertType
     * @param array $customerIds
     * @param int $websiteId
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function processAlerts(string $alertType, array $customerIds, int $websiteId): array
    {
        /** @var Email $email */
        $email = $this->emailFactory->create();
        $email->setType($alertType);
        $email->setWebsiteId($websiteId);
        $errors = [];

        /** @var Website $website */
        $website = $this->storeManager->getWebsite($websiteId);

        foreach ($website->getStores() as $store) {
            /** @var CustomerInterface $customer */
            $customer = null;
            $products = [];
            $storeId = (int)$store->getId();
            $email->setStoreId($storeId);

            try {
                $collection = $this->getAlertCollection($alertType, $customerIds, $storeId);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
                continue;
            }

            /** @var Price|Stock $alert */
            foreach ($collection as $alert) {
                try {
                    if ($customer === null) {
                        $customer = $this->customerRepository->getById($alert->getCustomerId());
                    } elseif ((int)$customer->getId() !== (int)$alert->getCustomerId()) {
                        $this->sendEmail($customer, $email);
                        $customer = $this->customerRepository->getById($alert->getCustomerId());
                    }

                    if (!isset($products[$alert->getProductId()])) {
                        $product = $this->productRepository->getById($alert->getProductId(), false, $storeId, true);
                        $products[$alert->getProductId()] = $product;
                    } else {
                        $product = $products[$alert->getProductId()];
                    }

                    switch ($alertType) {
                        case self::ALERT_TYPE_STOCK:
                            $this->saveStockAlert($alert, $product, $website, $email);
                            break;
                        case self::ALERT_TYPE_PRICE:
                            $this->savePriceAlert($alert, $product, $customer, $email);
                            break;
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if ($customer !== null) {
                try {
                    $this->sendEmail($customer, $email);
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return $errors;
    }

    /**
     * Validate Alert Type
     *
     * @param string $alertType
     * @throws \InvalidArgumentException
     */
    private function validateAlertType(string $alertType): void
    {
        if (!in_array($alertType, [self::ALERT_TYPE_STOCK, self::ALERT_TYPE_PRICE])) {
            throw new \InvalidArgumentException('Invalid alert type');
        }
    }

    /**
     * Get alert collection
     *
     * @param string $alertType
     * @param array $customerIds
     * @param int $storeId
     * @return AbstractCollection
     * @throws \InvalidArgumentException
     */
    private function getAlertCollection(string $alertType, array $customerIds, int $storeId): AbstractCollection
    {
        switch ($alertType) {
            case self::ALERT_TYPE_STOCK:
                $collection = $this->stockCollectionFactory->create();
                $collection->addFieldToFilter('customer_id', ['in' => $customerIds])
                    ->addStatusFilter(0)
                    ->addFilter('store_id', $storeId)
                    ->setCustomerOrder()
                    ->addOrder('product_id');
                break;
            case self::ALERT_TYPE_PRICE:
                $collection = $this->priceCollectionFactory->create();
                $collection->addFieldToFilter('customer_id', ['in' => $customerIds])
                    ->addFilter('store_id', $storeId)
                    ->setCustomerOrder()
                    ->addOrder('product_id');
                break;
            default:
                throw new \InvalidArgumentException('Invalid alert type');
        }

        return $collection;
    }

    /**
     * Save Price Alert
     *
     * @param Price $alert
     * @param ProductInterface $product
     * @param CustomerInterface $customer
     * @param Email $email
     */
    private function savePriceAlert(
        Price $alert,
        ProductInterface $product,
        CustomerInterface $customer,
        Email $email
    ): void {
        $product->setCustomerGroupId($customer->getGroupId());
        $finalPrice = $product->getFinalPrice();
        if ($alert->getPrice() <= $finalPrice) {
            return;
        }

        $product->setFinalPrice($this->catalogData->getTaxPrice($product, $finalPrice));
        $product->setPrice($this->catalogData->getTaxPrice($product, $product->getPrice()));

        $alert->setPrice($finalPrice);
        $alert->setLastSendDate(date(DateTime::DATETIME_PHP_FORMAT));
        $alert->setSendCount($alert->getSendCount() + 1);
        $alert->setStatus(1);
        $alert->save();

        $email->addPriceProduct($product);
    }

    /**
     * Save stock alert
     *
     * @param Stock $alert
     * @param ProductInterface $product
     * @param WebsiteInterface $website
     * @param Email $email
     */
    private function saveStockAlert(
        Stock $alert,
        ProductInterface $product,
        WebsiteInterface $website,
        Email $email
    ): void {
        if (!$this->productSalability->isSalable($product, $website)) {
            return;
        }

        $alert->setSendDate(date(DateTime::DATETIME_PHP_FORMAT));
        $alert->setSendCount($alert->getSendCount() + 1);
        $alert->setStatus(1);
        $alert->save();

        $email->addStockProduct($product);
    }

    /**
     * Send alert email
     *
     * @param CustomerInterface $customer
     * @param Email $email
     */
    private function sendEmail(CustomerInterface $customer, Email $email): void
    {
        $email->setCustomerData($customer);
        $email->send();
        $email->clean();
    }
}
