<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Http;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * A client that can communicate with the Authorize.net API
 */
class Client
{
    const API_ENDPOINT_URL = 'https://api.authorize.net/xml/v1/request.api';

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var PayloadConverter
     */
    private $payloadConverter;

    /**
     * @param ZendClientFactory $httpClientFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param PayloadConverter $payloadConverter
     */
    public function __construct(
        ZendClientFactory $httpClientFactory,
        ScopeConfigInterface $scopeConfig,
        PayloadConverter $payloadConverter
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->scopeConfig = $scopeConfig;
        $this->payloadConverter = $payloadConverter;
    }

    /**
     * Post request to gateway and return response
     *
     * @param array $request
     * @return array
     * @throws LocalizedException
     * @throws RuntimeException
     */
    public function sendRequest(array $request): array
    {
        $client = $this->httpClientFactory->create();
        $url = $this->getConfigValue('api_url') ?: self::API_ENDPOINT_URL;
        $client->setUri($url);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);

        $client->setRawData($this->payloadConverter->convertArrayToXml($request), 'text/xml');
        $client->setMethod(\Zend_Http_Client::POST);

        try {
            $responseBody = $client->request()->getBody();
            $response = $this->payloadConverter->convertXmlToArray($responseBody);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Something went wrong in the payment gateway.')
            );
        }

        return $response;
    }

    /**
     * Retrieves a value from the config from the current module's config values
     *
     * @param string $field The field within this modules config to retrieve
     * @return string|null
     */
    private function getConfigValue(string $field): ?string
    {
        // @TODO refactor this into a Config object
        return $this->scopeConfig->getValue('payment/authorizenet_acceptjs/' . $field, ScopeInterface::SCOPE_STORE);
    }
}
