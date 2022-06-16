<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\TotalsCollector;

/**
 * Plugin to reset stored totals before a top-level resolver runs
 */
class ResetStoredTotalsBeforeTopResolver
{
    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * @param TotalsCollector $totalsCollector
     */
    public function __construct(TotalsCollector $totalsCollector)
    {
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * If this resolver is at the top level, clear any stored totals so they're forced to be recalculated
     *
     * @param ResolverInterface $subject
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResolve(
        ResolverInterface $subject,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($info->isTopResolver()) {
            $this->totalsCollector->clearTotals();
        }
    }
}
