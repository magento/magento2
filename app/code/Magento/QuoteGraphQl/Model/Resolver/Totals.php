<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartTotalRepositoryInterface;

/**
 * @inheritdoc
 */
class Totals implements ResolverInterface
{
    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalRepository;

    /**
     * @param CartTotalRepositoryInterface $cartTotalRepository
     */
    public function __construct(
        CartTotalRepositoryInterface $cartTotalRepository
    ) {
        $this->cartTotalRepository = $cartTotalRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $cartTotals = $this->cartTotalRepository->get($value['model']->getId());

        $currency = $cartTotals->getQuoteCurrencyCode();
        $data = $this->addCurrencyCode([
            'grand_total' => ['value' => $cartTotals->getGrandTotal(), ],
            'subtotal_including_tax' => ['value' => $cartTotals->getSubtotalInclTax()],
            'subtotal_excluding_tax' => ['value' => $cartTotals->getSubtotal()],
            'subtotal_with_discount_excluding_tax' => ['value' => $cartTotals->getSubtotalWithDiscount()]
        ], $currency);

        $data['model'] = $value['model'];

        return $data;
    }

    /**
     * Adds currency code to the totals
     *
     * @param array $totals
     * @param string|null $currencyCode
     * @return array
     */
    private function addCurrencyCode(array $totals, $currencyCode): array
    {
        foreach ($totals as &$total) {
            $total['currency'] = $currencyCode;
        }

        return $totals;
    }
}
