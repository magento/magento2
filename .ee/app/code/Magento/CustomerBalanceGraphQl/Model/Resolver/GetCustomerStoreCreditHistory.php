<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerBalanceGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\CustomerBalance\Model\Balance\HistoryFactory;
use Magento\CustomerBalance\Model\ResourceModel\Balance\History\CollectionFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CustomerBalance\Helper\Data as CustomerBalanceHelper;

/**
 * Resolver for checking applied Store credit balance
 */
class GetCustomerStoreCreditHistory implements ResolverInterface
{
    const ACTION_UPDATED = 1;

    const ACTION_CREATED = 2;

    const ACTION_USED = 3;

    const ACTION_REFUNDED = 4;

    const ACTION_REVERTED = 5;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CustomerBalanceHelper
     */
    private $customerBalanceHelper;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param HistoryFactory $historyFactory
     * @param CollectionFactory $collectionFactory
     * @param CustomerBalanceHelper $customerBalanceHelper
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        HistoryFactory $historyFactory,
        CollectionFactory $collectionFactory,
        CustomerBalanceHelper $customerBalanceHelper
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->historyFactory = $historyFactory;
        $this->collectionFactory = $collectionFactory;
        $this->customerBalanceHelper = $customerBalanceHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $customerId = $context->getUserId();

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        return $this->canShowHistory() ? $this->getBalanceHistoryArray(
            $customerId,
            $store,
            $args['pageSize'] ?? 20,
            $args['currentPage'] ?? 1
        ) : null;
    }

    /**
     * Check if settings for history along with customer balance are enabled
     *
     * @return bool
     */
    private function canShowHistory(): bool
    {
        return $this->customerBalanceHelper->isEnabled() && $this->customerBalanceHelper->isHistoryEnabled();
    }

    /**
     * Retrieve history events
     *
     * @param int $customerId
     * @param StoreInterface $store
     * @param int $pageSize
     * @param int $currentPage
     * @return array
     * @throws GraphQlInputException
     */
    private function getBalanceHistoryArray(
        int $customerId,
        StoreInterface $store,
        int $pageSize = 20,
        int $currentPage = 1
    ): array {
        $currentCurrency = $store->getCurrentCurrency();
        $headerNamesArray = $this->getActionNamesArray();

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'customer_id',
            $customerId
        )->addFieldToFilter(
            'website_id',
            $store->getWebsiteId()
        )->addOrder(
            'updated_at',
            'DESC'
        )->addOrder(
            'history_id',
            'DESC'
        )->setCurPage($currentPage)
        ->setPageSize($pageSize);

        $collectionTotal = $this->collectionFactory->create();
        $collectionTotal->addFieldToFilter(
            'customer_id',
            $customerId
        )->addFieldToFilter(
            'website_id',
            $store->getWebsiteId()
        )->addFieldToSelect('history_id');

        $historyArray = [];
        foreach ($collection as $event) {
            $itemArray = [
              'action' => $headerNamesArray[$event->getAction()],
              'balance_change' => [
                  'value' => $this->priceCurrency->convert($event->getBalanceDelta(), $store),
                  'currency' => $currentCurrency->getCode(),
                  'formatted' => $this->priceCurrency->format($this->priceCurrency->convert($event->getBalanceDelta(), $store),false,null,null,$currentCurrency->getCode())
              ],
              'actual_balance' => [
                  'value' => $this->priceCurrency->convert($event->getBalanceAmount(), $store),
                  'currency' => $currentCurrency->getCode(),
                  'formatted' => $this->priceCurrency->format($this->priceCurrency->convert($event->getBalanceAmount(), $store),false,null,null,$currentCurrency->getCode())
              ],
              'date_time_changed' => $event->getUpdatedAt(),
            ];
            $historyArray[] = $itemArray;
        }
        $totalItems = $collectionTotal->count();

        //possible division by 0
        if ($pageSize) {
            $maxPages = ceil($totalItems / $pageSize);
        } else {
            $maxPages = 0;
        }

        if ($currentPage > $maxPages && $totalItems > 0) {
            $currentPage = new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the number of pages available.',
                    [$maxPages]
                )
            );
        }

        return [
            'items' => $historyArray,
            'page_info' => [
                'current_page' => $currentPage,
                'page_size' => $pageSize,
                'total_pages' => $maxPages,
            ],
            'total_count' => $totalItems
        ];
    }

    /**
     * Available action names getter
     *
     * @return array
     */
    private function getActionNamesArray(): array
    {
        return [
            self::ACTION_CREATED => __('Created'),
            self::ACTION_UPDATED => __('Updated'),
            self::ACTION_USED => __('Used'),
            self::ACTION_REFUNDED => __('Refunded'),
            self::ACTION_REVERTED => __('Reverted')
        ];
    }
}
