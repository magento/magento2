<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\Cart;

use Magento\Framework\Locale\ResolverInterface;

/**
 * Cart request quantity processor
 */
class RequestQuantityProcessor
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * RequestQuantityProcessor constructor.
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        ResolverInterface $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    /**
     * Process cart request data
     *
     * @param array $cartData
     * @return array
     */
    public function process(array $cartData): array
    {
        $filter = new \Laminas\I18n\Filter\NumberParse($this->localeResolver->getLocale());

        foreach ($cartData as $index => $data) {
            if (isset($data['qty'])) {
                $data['qty'] = is_string($data['qty']) ? trim($data['qty']) : $data['qty'];
                $cartData[$index]['qty'] = $filter->filter($data['qty']);
            }
        }

        return $cartData;
    }
}
