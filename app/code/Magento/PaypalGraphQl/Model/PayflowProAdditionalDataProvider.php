<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;
use Magento\Paypal\Model\Config;

/**
 * Get payment additional data for Payflow pro payment
 */
class PayflowProAdditionalDataProvider implements AdditionalDataProviderInterface
{

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * Returns additional data
     *
     * @param array $args
     * @return array
     */
    public function getData(array $args): array
    {
        if (isset($args[Config::METHOD_PAYFLOWPRO])) {
            return $args[Config::METHOD_PAYFLOWPRO];
        }
        return [];
    }
}
