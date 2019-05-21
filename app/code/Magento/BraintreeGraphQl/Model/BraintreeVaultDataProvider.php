<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BraintreeGraphQl\Model;

use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;
use Magento\Framework\Stdlib\ArrayManager;

class BraintreeVaultDataProvider implements AdditionalDataProviderInterface
{
    private const PATH_ADDITIONAL_DATA = 'input/payment_method/additional_data/braintree_cc_vault';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    public function getData(array $args): array
    {
        return $this->arrayManager->get(static::PATH_ADDITIONAL_DATA, $args) ?? [];
    }
}
