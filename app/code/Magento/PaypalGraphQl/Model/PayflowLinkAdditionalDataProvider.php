<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Url\Validator as UrlValidator;
use Magento\Paypal\Model\Config;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

/**
 * Get payment additional data for Paypal Payflow Link payment
 */
class PayflowLinkAdditionalDataProvider implements AdditionalDataProviderInterface
{
    /**
     * @var UrlValidator
     */
    private $urlValidator;

    /**
     * @param UrlValidator $urlValidator
     */
    public function __construct(UrlValidator $urlValidator)
    {
        $this->urlValidator = $urlValidator;
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
        $additionalData = $data[Config::METHOD_PAYFLOWLINK] ?? [];
        $this->validateUrls($additionalData);

        return $additionalData;
    }

    /**
     * Validate redirect urls
     *
     * @param array $data
     * @throws GraphQlInputException
     */
    private function validateUrls(array $data): void
    {
        $urlKeys = ['cancel_url', 'return_url', 'error_url'];

        foreach ($urlKeys as $urlKey) {
            if (isset($data[$urlKey])) {
                if (!$this->urlValidator->isValid($data[$urlKey])) {
                    $errorMessage = $this->urlValidator->getMessages()['invalidUrl'] ?? "Invalid Url.";
                    throw new GraphQlInputException(__($errorMessage));
                }
            }
        }
    }
}
