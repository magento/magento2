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
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;

/**
 * @inheritdoc
 */
class CartTaxes implements ResolverInterface
{
    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @param TotalsCollector $totalsCollector
     */
    public function __construct(
        TotalsCollector $totalsCollector
    ) {
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $data = [];

        /** @var Quote $quote */
        $quote = $value['model'];
        $appliedTaxes = $this->totalsCollector->collectQuoteTotals($value['model'])->getAppliedTaxes();

        if (count($appliedTaxes) == 0) {
            return [];
        }

        $currency = $quote->getQuoteCurrencyCode();
        foreach ($appliedTaxes as $appliedTax) {
            $data[] = [
                'label' => $appliedTax['id'],
                'amount' => ['value' => $appliedTax['amount'], 'currency' => $currency]
            ];
        }

        return $data;
    }
}
