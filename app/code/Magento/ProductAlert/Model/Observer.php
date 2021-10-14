<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Select;
use Magento\ProductAlert\Model\Mailing\AlertProcessor;
use Magento\ProductAlert\Model\Mailing\Publisher;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory as StockCollectionFactory;
use Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory as PriceCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Product Alert observer
 */
class Observer
{
    /**
     * Error email template configuration
     *
     * @deprecated
     * @see \Magento\ProductAlert\Model\Mailing\ErrorEmailSender::XML_PATH_ERROR_TEMPLATE
     */
    const XML_PATH_ERROR_TEMPLATE = 'catalog/productalert_cron/error_email_template';

    /**
     * Error email identity configuration
     *
     * @deprecated
     * @see \Magento\ProductAlert\Model\Mailing\ErrorEmailSender::XML_PATH_ERROR_IDENTITY
     */
    const XML_PATH_ERROR_IDENTITY = 'catalog/productalert_cron/error_email_identity';

    /**
     * 'Send error emails to' configuration
     *
     * @deprecated
     * @see \Magento\ProductAlert\Model\Mailing\ErrorEmailSender::XML_PATH_ERROR_RECIPIENT
     */
    const XML_PATH_ERROR_RECIPIENT = 'catalog/productalert_cron/error_email';

    /**
     * Allow price alert
     *
     */
    const XML_PATH_PRICE_ALLOW = 'catalog/productalert/allow_price';

    /**
     * Allow stock alert
     *
     */
    const XML_PATH_STOCK_ALLOW = 'catalog/productalert/allow_stock';

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceCollectionFactory
     */
    private $priceCollectionFactory;

    /**
     * @var StockCollectionFactory
     */
    private $stockCollectionFactory;

    /**
     * @var Publisher
     */
    private $publisher;


    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param PriceCollectionFactory $priceCollectionFactory
     * @param StockCollectionFactory $stockCollectionFactory
     * @param Publisher $publisher
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        PriceCollectionFactory $priceCollectionFactory,
        StockCollectionFactory $stockCollectionFactory,
        Publisher $publisher
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->publisher = $publisher;
        $this->priceCollectionFactory = $priceCollectionFactory;
        $this->stockCollectionFactory = $stockCollectionFactory;
    }

    /**
     * Process product alerts
     */
    public function process(): void
    {
        $alertTypes = [AlertProcessor::ALERT_TYPE_PRICE, AlertProcessor::ALERT_TYPE_STOCK];
        foreach ($alertTypes as $alertType) {
            foreach ($this->storeManager->getWebsites() as $website) {
                if (!$website->getDefaultGroup() || !$website->getDefaultGroup()->getDefaultStore()) {
                    continue;
                }
                $storeId = $website->getDefaultGroup()->getDefaultStore()->getId();

                $allowConfigPath = $alertType === AlertProcessor::ALERT_TYPE_PRICE
                    ? self::XML_PATH_PRICE_ALLOW : self::XML_PATH_STOCK_ALLOW;
                if (!$this->scopeConfig->getValue($allowConfigPath, ScopeInterface::SCOPE_STORE, $storeId)) {
                    continue;
                }

                $customerIds = $this->loadCustomerIds($alertType, (int)$website->getId());
                if (!empty($customerIds)) {
                    $this->publisher->execute($alertType, $customerIds, (int)$website->getId());
                }
            }
        }
    }

    /**
     * Load alert customers
     *
     * @param string $alertType
     * @param int $websiteId
     * @return array
     */
    private function loadCustomerIds(string $alertType, int $websiteId): array
    {
        switch ($alertType) {
            case AlertProcessor::ALERT_TYPE_PRICE:
                $alertCollection = $this->priceCollectionFactory->create();
                break;
            case AlertProcessor::ALERT_TYPE_STOCK:
                $alertCollection = $this->stockCollectionFactory->create();
                break;
            default:
                return [];
        }

        $select = $alertCollection->getSelect();
        $select->reset(Select::COLUMNS);
        $select->columns('customer_id')
            ->group('customer_id');
        $select->where('website_id = ?', $websiteId);
        if ($alertType === AlertProcessor::ALERT_TYPE_STOCK) {
            $select->where('status = ?', 0);
        }

        return $alertCollection->getConnection()->fetchCol($select);
    }
}
