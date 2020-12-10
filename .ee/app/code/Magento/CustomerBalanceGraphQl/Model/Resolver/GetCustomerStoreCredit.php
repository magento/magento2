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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\CustomerBalance\Helper\Data as CustomerBalanceHelper;

/**
 * Resolver for checking applied Store credit balance
 */
class GetCustomerStoreCredit implements ResolverInterface
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var BalanceFactory
     */
    private $balanceFactory;

    /**
     * @var CustomerBalanceHelper
     */
    private $customerBalanceHelper;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param BalanceFactory $balanceFactory
     * @param CustomerBalanceHelper $customerBalanceHelper
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        BalanceFactory $balanceFactory,
        CustomerBalanceHelper $customerBalanceHelper
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->balanceFactory = $balanceFactory;
        $this->customerBalanceHelper = $customerBalanceHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $currentCurrency = $store->getCurrentCurrency();
        $customerId = $context->getUserId();
        return [
            'enabled' => $this->customerBalanceHelper->isEnabled(),
            'current_balance' => $this->customerBalanceHelper->isEnabled() ? [
                'value' => $this->getCustomerBalance(
                    $customerId,
                    (int)$store->getWebsiteId(),
                    (int)$store->getId()
                ),
                'currency' => $currentCurrency->getCode(),
                'formatted' => $this->priceCurrency->format($this->getCustomerBalance(
                    $customerId,
                    (int)$store->getWebsiteId(),
                    (int)$store->getId()
                ),false,null,null,$currentCurrency->getCode())
            ] : null
        ];
    }

    /**
     * Return store credit for customer
     *
     * @param int $customerId
     * @param int $websiteId
     * @param int $storeId
     * @return float
     * @throws LocalizedException
     */
    private function getCustomerBalance($customerId, int $websiteId, int $storeId): float
    {
        $baseBalance = $this->balanceFactory->create()
            ->setCustomerId($customerId)
            ->setWebsiteId($websiteId)
            ->loadByCustomer()
            ->getAmount();
        $customerBalance = $this->priceCurrency->convert($baseBalance, $storeId);
        return $customerBalance;
    }
}
