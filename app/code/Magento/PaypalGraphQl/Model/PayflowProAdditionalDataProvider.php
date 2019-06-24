<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

/**
 * Get payment additional data for Payflow Pro payment method
 */
class PayflowProAdditionalDataProvider implements AdditionalDataProviderInterface
{

    private const PATH_ADDITIONAL_DATA = 'input/payment_method/additional_data';

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
        $additionalData = $this->arrayManager->get(self::PATH_ADDITIONAL_DATA, $args) ?? [];

        return $additionalData;
    }
}
