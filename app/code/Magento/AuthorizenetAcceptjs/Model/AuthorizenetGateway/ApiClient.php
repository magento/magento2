<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\RequestFactory;
use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\ResponseFactory;

/**
 * A client that can communicate with the Authorize.net API
 */
class ApiClient
{
    const API_ENDPOINT_URL = 'https://api.authorize.net/xml/v1/request.api';

    /**
     * Request factory
     *
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * Response factory
     *
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param RequestFactory $requestFactory
     * @param ResponseFactory $responseFactory
     * @param ZendClientFactory $httpClientFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        RequestFactory $requestFactory,
        ResponseFactory $responseFactory,
        ZendClientFactory $httpClientFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->httpClientFactory = $httpClientFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return a request stub with populated credentials
     *
     * @return Request
     */
    public function createAuthenticatedRequest(): Request
    {
        $request = $this->requestFactory->create()
            ->setData('merchantAuthentication', [
                'login' => $this->getConfigValue('login'),
                'transactionKey' => $this->getConfigValue('trans_key')
            ]);

        return $request;
    }

    /**
     * Post request to gateway and return response
     *
     * @param Request $request
     * @return Response
     * @throws LocalizedException
     * @throws RuntimeException
     */
    public function sendRequest(Request $request)
    {
        /** @var Response $response */
        $response = $this->responseFactory->create();
        $client = $this->httpClientFactory->create();
        $url = $this->getConfigValue('api_url') ?: self::API_ENDPOINT_URL;
        $client->setUri($url);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);

        $client->setParameterPost($request->toApiXml());
        $client->setMethod(\Zend_Http_Client::POST);

        try {
            $responseBody = $client->request()->getBody();
            $response->hydrateWithXml($responseBody);
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
        return $this->scopeConfig->getValue('payment/authorizenet_acceptjs/' . $field, ScopeInterface::SCOPE_STORE);
    }
}
