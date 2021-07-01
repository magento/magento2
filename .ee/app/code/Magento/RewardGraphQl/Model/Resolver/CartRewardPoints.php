<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RewardGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\NegotiableQuote\Model\PriceCurrency;

/**
 * @inheritdoc
 */
class CartRewardPoints implements ResolverInterface
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * CartRewardPoints constructor.
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(PriceCurrency $priceCurrency){
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Fetch applied reward points to cart
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $quote = $value['model'];
        if ($quote->getUseRewardPoints()) {
            return [
                'money' => [
                    'currency' => $quote->getQuoteCurrencyCode(),
                    'value' => $quote->getRewardCurrencyAmount(),
                    'formatted' => $this->priceCurrency->format($quote->getRewardCurrencyAmount(),false,null,null,$quote->getQuoteCurrencyCode())
                ],
                'points' => $quote->getRewardPointsBalance()
            ];
        }

        return null;
    }
}
