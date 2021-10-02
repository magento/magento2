<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerBalanceGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\CustomerBalance\Helper\Data as CustomerBalanceHelper;

/**
 * Resolver for checking applied Store credit balance
 */
class GetAppliedStoreCreditFromCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

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
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param BalanceFactory $balanceFactory
     * @param CustomerBalanceHelper $customerBalanceHelper
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository,
        PriceCurrencyInterface $priceCurrency,
        BalanceFactory $balanceFactory,
        CustomerBalanceHelper $customerBalanceHelper
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartRepository = $cartRepository;
        $this->priceCurrency = $priceCurrency;
        $this->balanceFactory = $balanceFactory;
        $this->customerBalanceHelper = $customerBalanceHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $cart = $value['model'];
        $cartId = $cart->getId();
        $quote = $this->cartRepository->get($cartId);
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $currentCurrency = $store->getCurrentCurrency();
        $customerId = $context->getUserId();
        if (empty($customerId)) {
            throw new GraphQlAuthorizationException(__('Please specify a valid customer'));
        }
        $customerBalance = $this->getCustomerBalance(
            $customerId,
            (int)$store->getWebsiteId(),
            (int)$store->getId()
        );
        $balanceApplied = $quote->getCustomerBalanceAmountUsed();

        return [
            'enabled' => $this->customerBalanceHelper->isEnabled(),
            'current_balance' => $this->customerBalanceHelper->isEnabled() ? [
                'value' => $customerBalance,
                'currency' => $currentCurrency->getCode(),
                'formatted' => $this->priceCurrency->format($customerBalance,false,null,null,$currentCurrency->getCode())
            ] : null,
            'applied_balance' => [
                'value' => $balanceApplied,
                'currency' => $currentCurrency->getCode(),
                'formatted' => $this->priceCurrency->format($balanceApplied,false,null,null,$currentCurrency->getCode())
            ]
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
