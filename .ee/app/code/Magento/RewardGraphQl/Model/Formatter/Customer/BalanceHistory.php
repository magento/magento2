<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RewardGraphQl\Model\Formatter\Customer;

use Magento\Customer\Model\Customer;
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Reward\Model\ResourceModel\Reward\History\Collection as RewardHistoryCollection;
use Magento\Reward\Model\ResourceModel\Reward\History\CollectionFactory as RewardHistoryCollectionFactory;
use Magento\Reward\Model\Reward;
use Magento\Reward\Model\Reward\History;
use Magento\RewardGraphQl\Model\Config;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Format Customer Reward Points Balance History
 */
class BalanceHistory implements FormatterInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RewardHistoryCollectionFactory
     */
    private $rewardHistoryCollectionFactory;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param Config $config
     * @param RewardHistoryCollectionFactory $rewardHistoryCollectionFactory
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        Config $config,
        RewardHistoryCollectionFactory $rewardHistoryCollectionFactory,
        PriceCurrency $priceCurrency
    ) {
        $this->config = $config;
        $this->rewardHistoryCollectionFactory = $rewardHistoryCollectionFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @inheritdoc
     */
    public function format(Customer $customer, StoreInterface $store, Reward $rewardInstance): array
    {
        if (!$this->config->customersMaySeeHistory((int)$store->getWebsite()->getId())) {
            return [];
        }

        $balanceHistory = [];
        $rewardHistoryCollection = $this->getHistoryCollection(
            (int)$customer->getId(),
            (int)$store->getWebsite()->getId()
        );

        /** @var History $rewardHistoryItem */
        foreach ($rewardHistoryCollection as $rewardHistoryItem) {
            $balanceHistory[] = [
                'balance' => [
                    'money' => [
                        'currency' => $store->getCurrentCurrency()->getCode(),
                        'value' => $rewardHistoryItem->getCurrencyAmount(),
                        'formatted' => $this->priceCurrency->format($rewardHistoryItem->getCurrencyAmount(),false,null,null,$store->getCurrentCurrency()->getCode())
                    ],
                    'points' => $rewardHistoryItem->getPointsBalance()
                ],
                'change_reason' => $rewardHistoryItem->getMessage(),
                'date' => $rewardHistoryItem->getCreatedAt(),
                'points_change' => $rewardHistoryItem->getPointsDelta()
            ];
        }

        return $balanceHistory;
    }

    /**
     * Get reward history for a given customer in a given website
     *
     * @param int $customerId
     * @param int $websiteId
     * @return RewardHistoryCollection
     */
    private function getHistoryCollection(int $customerId, int $websiteId): RewardHistoryCollection
    {
        return $this->rewardHistoryCollectionFactory->create()
            ->addCustomerFilter($customerId)
            ->addWebsiteFilter($websiteId)
            ->setExpiryConfig($this->config->getExpirationDetails())
            ->addExpirationDate($websiteId)
            ->skipExpiredDuplicates()
            ->setDefaultOrder();
    }
}
