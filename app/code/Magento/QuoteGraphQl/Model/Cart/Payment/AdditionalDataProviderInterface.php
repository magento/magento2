<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart\Payment;

/**
 * Interface for payment method additional data provider
 */
interface AdditionalDataProviderInterface
{
    /**
     * Returns Additional Data
     *
     * @param array $args
     * @return array
     */
    public function getData(array $args): array;
}