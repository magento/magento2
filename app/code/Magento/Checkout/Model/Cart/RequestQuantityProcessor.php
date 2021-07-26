<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\Cart;

use Magento\Framework\Locale\ResolverInterface;
use NumberFormatter;

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
        foreach ($cartData as $index => $data) {
            if (isset($data['qty'])) {
                $data['qty'] = $this->prepareQuantity($data['qty']);
                $data['qty'] = is_string($data['qty']) ? trim($data['qty']) : $data['qty'];
                $cartData[$index]['qty'] = $this->filter($data['qty']);
            }
        }

        return $cartData;
    }

    /**
     * Prepare quantity with taking into account decimal separator by locale
     *
     * @param int|float|string|array $quantity
     * @return int|float|string|array
     */
    public function prepareQuantity($quantity)
    {
        $formatter = new NumberFormatter($this->localeResolver->getLocale(), NumberFormatter::CURRENCY);
        $decimalSymbol = $formatter->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);

        if (is_array($quantity)) {
            foreach ($quantity as $key => $qty) {
                if (strpos((string)$qty, '.') !== false && $decimalSymbol !== '.') {
                    $quantity[$key] = str_replace('.', $decimalSymbol, $qty);
                }
            }
        } else {
            if (strpos((string)$quantity, '.') !== false && $decimalSymbol !== '.') {
                $quantity = str_replace('.', $decimalSymbol, (string) $quantity);
            }
        }

        return $quantity;
    }

    /**
     * Filter quantity value and parse it by region if needed.
     *
     * @param float|int|array|string $quantity
     *
     * @return float|int|array|string
     */
    private function filter($quantity)
    {
        $formatter = new NumberFormatter($this->localeResolver->getLocale(), NumberFormatter::DEFAULT_STYLE);

        if (is_array($quantity)) {
            foreach ($quantity as $key => $qty) {
                $quantity[$key] = $this->parseFormat($qty, $formatter);
            }
        } else {
            $quantity = $this->parseFormat($quantity, $formatter);
        }

        return $quantity;
    }

    /**
     * Phrase quantity value if needed.
     *
     * @param float|int|string $quantity
     * @param NumberFormatter $formatter
     *
     * @return float|int|string
     */
    private function parseFormat($quantity, NumberFormatter $formatter)
    {
        if (!is_float($quantity) && !is_int($quantity)) {
            return $formatter->parse($quantity);
        }

        return $quantity;
    }
}
