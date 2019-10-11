<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Paypal\Model\Config;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;
use Magento\PaypalGraphQl\Model\Resolver\Store\Url;

/**
 * Get payment additional data for Paypal Payflow Link payment method
 */
class PayflowLinkAdditionalDataProvider implements AdditionalDataProviderInterface
{
    /**
     * @var Url
     */
    private $urlService;

    /**
     * @param Url $urlService
     */
    public function __construct(Url $urlService)
    {
        $this->urlService = $urlService;
    }

    /**
     * Return additional data from payflow_link paymentMethodInput
     *
     * @param array $data
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $data): array
    {
        $additionalData = $data[Config::METHOD_PAYFLOWLINK] ?? [];
        $this->validatePathsInArray($additionalData);

        return $additionalData;
    }

    /**
     * Validate paths in known keys of the additional data array
     *
     * @param array $data
     * @throws GraphQlInputException
     */
    private function validatePathsInArray(array $data): void
    {
        $urlKeys = ['cancel_url', 'return_url', 'error_url'];

        foreach ($urlKeys as $urlKey) {
            if (isset($data[$urlKey])) {
                if (!$this->urlService->isPath($data[$urlKey])) {
                    throw new GraphQlInputException(__('Invalid Url.'));
                }
            }
        }
    }
}
