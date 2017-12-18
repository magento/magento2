<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model\Connector\Http;

use Magento\ReleaseNotification\Model\ContentProviderInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Model\Auth\Session;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\ClientInterface;

/**
 * Class HttpContentProvider
 *
 * Requests the release notification content data via an HTTP call to a REST API
 */
class HttpContentProvider implements ContentProviderInterface
{
    /**
     * Path to the configuration value which contains an URL that provides the release notification data.
     *
     * @var string
     */
    private static $notificationUrlConfigPath = 'releaseNotification/url/content';

    /**
     * Version query parameter value for default release notification content if version specific content is not found.
     *
     * @var string
     */
    private static $defaultContentVersion = 'default';

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ResponseResolver
     */
    private $responseResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ClientInterface $httpClient
     * @param ScopeConfigInterface $config
     * @param ProductMetadataInterface $productMetadata
     * @param Session $session
     * @param ResponseResolver $responseResolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $httpClient,
        ScopeConfigInterface $config,
        ProductMetadataInterface $productMetadata,
        Session $session,
        ResponseResolver $responseResolver,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->productMetadata = $productMetadata;
        $this->session = $session;
        $this->responseResolver = $responseResolver;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        $result = false;

        $url = $this->buildUrl(
            $this->config->getValue(self::$notificationUrlConfigPath),
            [
                'version' => $this->getTargetVersion(),
                'edition' => $this->productMetadata->getEdition(),
                'locale' => $this->session->getUser()->getInterfaceLocale()
            ]
        );

        try {
            $response = $this->getResponse($url);
            if ($response == "[]") {
                $response = $this->getDefaultContent();
            }
            $status = $this->httpClient->getStatus();
            $result = $this->responseResolver->getResult($response, $status);
        } catch (\Exception $e) {
            $this->logger->warning(
                sprintf(
                    'Retrieving the release notification content from the Magento Marketing service has failed: %s',
                    !empty($response) ? $response : 'Response body is empty.'
                )
            );
            $this->logger->critical(
                new \Exception(
                    sprintf('Magento Marketing service CURL connection error: %s', $e->getMessage())
                )
            );
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getTargetVersion()
    {
        $metadataVersion = $this->productMetadata->getVersion();
        $version = strstr($metadataVersion, '-', true);

        return !$version ? $metadataVersion : $version;
    }

    /**
     * @inheritdoc
     */
    public function getEdition()
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * @inheritdoc
     */
    public function getLocale()
    {
        return $this->session->getUser()->getInterfaceLocale();
    }

    /**
     * Returns the default content as a fallback if there is no content retrieved from the service/
     *
     * @return string
     */
    private function getDefaultContent()
    {
        $url = $this->buildUrl(
            $this->config->getValue(self::$notificationUrlConfigPath),
            [
                'version' => self::$defaultContentVersion,
                'edition' => $this->getEdition(),
                'locale' => $this->session->getUser()->getInterfaceLocale()
            ]
        );
        return $this->getResponse($url);
    }

    /**
     * Returns the response body from the HTTP client
     *
     * @param $url
     *
     * @return string
     */
    private function getResponse($url)
    {
        $this->httpClient->get($url);
        return $this->httpClient->getBody();
    }

    /**
     * Builds the URL to request the release notification content data based on passed query parameters.
     *
     * @param $baseUrl
     * @param $queryData
     * @return string
     */
    private function buildUrl($baseUrl, $queryData)
    {
        $query = http_build_query($queryData, '', '&');
        $baseUrl .= '?' . $query;
        return $baseUrl;
    }
}
