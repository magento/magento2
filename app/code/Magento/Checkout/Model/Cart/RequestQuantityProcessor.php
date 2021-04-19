<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\Cart;

use Magento\Framework\Locale\ResolverInterface;
use Zend_Locale_Data;

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
                $data['qty'] = $this->prepareQuantity($data['qty']);
                $data['qty'] = is_string($data['qty']) ? trim($data['qty']) : $data['qty'];
                $cartData[$index]['qty'] = $filter->filter($data['qty']);
            }
        }

        return $cartData;
    }

    /**
     * Prepare quantity with taking into account decimal separator by locale
     *
     * @param int|float|string|array $quantity
     * @return int|float|string|array
     * @throws \Zend_Locale_Exception
     */
    public function prepareQuantity($quantity)
    {
        $symbols = Zend_Locale_Data::getList($this->localeResolver->getLocale(),'symbols');

        if (is_array($quantity)) {
            foreach ($quantity as $key => $qty) {
                if (strpos((string)$qty, '.') !== false && $symbols['decimal'] !== '.') {
                    $quantity[$key] = str_replace('.', $symbols['decimal'], $qty);
                }
            }
        } else {
            if (strpos((string)$quantity, '.') !== false && $symbols['decimal'] !== '.') {
                $quantity = str_replace('.', $symbols['decimal'], (string)$quantity);
            }
        }

        return $quantity;
    }
}
