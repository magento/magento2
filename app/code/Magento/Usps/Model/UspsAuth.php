<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Usps\Model;

use Magento\Framework\App\Cache\Type\Config as Cache;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;

class UspsAuth extends AbstractCarrier
{
    public const CACHE_KEY_PREFIX = 'usps_api_token_';
    public const OAUTH_REQUEST_END_POINT = 'oauth2/v3/token';
    private const CONTENT_TYPE_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    private const ERROR_LOG_MESSAGE = '---Exception from auth api---';

    /**
     * @var AsyncClientInterface
     */
    private $httpClient;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var ErrorFactory
     */
    public $_rateErrorFactory;

    /**
     * @param Cache $cacheManager
     * @param ErrorFactory $rateErrorFactory
     * @param AsyncClientInterface|null $httpClient
     */
    public function __construct(
        Cache $cacheManager,
        ErrorFactory $rateErrorFactory,
        ?AsyncClientInterface $httpClient = null,
    ) {
        $this->cache = $cacheManager;
        $this->_rateErrorFactory = $rateErrorFactory;
        $this->httpClient = $httpClient ?? ObjectManager::getInstance()->get(AsyncClientInterface::class);
    }

    /**
     * Token Generation
     *
     * @param String $clientId
     * @param String $clientSecret
     * @param String $clientUrl
     * @return string|false|null
     * @throws LocalizedException
     * @throws \Throwable
     */
    public function getAccessToken(string $clientId, string $clientSecret, string $clientUrl): string|false|null
    {
        $cacheKey = self::CACHE_KEY_PREFIX;
        $accessToken = $this->cache->load($cacheKey);
        if (!$accessToken) {
            $headers = [
                'Content-Type' => self::CONTENT_TYPE_FORM_URLENCODED
            ];

            $authPayload = http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'prices shipments tracking labels payments international-labels'
            ]);
            try {
                $asyncResponse = $this->httpClient->request(new Request(
                    $clientUrl,
                    Request::METHOD_POST,
                    $headers,
                    $authPayload
                ));

                $responseResult = $asyncResponse->get();
                $response = $responseResult->getBody();
                $response = json_decode($response, true);

                if (!empty($response['access_token'])) {
                    $accessToken = $response['access_token'];
                    $this->cache->save($accessToken, $cacheKey, [], $response['expires_in'] ?: 10000);
                } else {
                    $debugData = ['request_type' => 'Access Token Request', 'result' => $response];
                    $this->_debug($debugData);
                    return false;
                }
            } catch (\Exception $e) {
                $this->_debug(self::ERROR_LOG_MESSAGE . $e->getMessage());
                return null;
            }
        }
        return $accessToken;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * phpcs:disable
     */
    public function collectRates(RateRequest $request)
    {
        return ''; // This block is empty as not required.
    }
}
