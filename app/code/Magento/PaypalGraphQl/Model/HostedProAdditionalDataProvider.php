<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\PaypalGraphQl\Model\Resolver\Store\Url as UrlService;
use Magento\Paypal\Model\Config;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

/**
 * Get payment additional data for Paypal HostedPro payment
 */
class HostedProAdditionalDataProvider implements AdditionalDataProviderInterface
{
    /**
     * @var UrlService
     */
    private $urlService;

    /**
     * @param UrlService $urlService
     */
    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    /**
     * Returns additional data
     *
     * @param array $data
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $data): array
    {
        $additionalData = $data[Config::METHOD_HOSTEDPRO] ?? [];
        $this->validateUrlPaths($additionalData);

        return $additionalData;
    }

    /**
     * Validate redirect url paths
     *
     * @param array $data
     * @throws GraphQlInputException
     */
    private function validateUrlPaths(array $data): void
    {
        $urlKeys = ['cancel_url', 'return_url'];

        foreach ($urlKeys as $urlKey) {
            if (isset($data[$urlKey])) {
                if (!$this->urlService->isPath($data[$urlKey])) {
                    throw new GraphQlInputException(__('Invalid Url.'));
                }
            }
        }
    }
}
