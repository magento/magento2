<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model\ContentProvider\Http;

use Magento\ReleaseNotification\Model\ContentProviderInterface;
use Magento\Setup\Module\I18n\Locale;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\ClientInterface;

/**
 * Requests the release notification content data via an HTTP call to a REST API
 */
class HttpContentProvider implements ContentProviderInterface
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @param ClientInterface $httpClient
     * @param UrlBuilder $urlBuilder
     * @param LoggerInterface $logger
     * @param int $requestTimeout
     */
    public function __construct(
        ClientInterface $httpClient,
        UrlBuilder $urlBuilder,
        LoggerInterface $logger,
        int $requestTimeout = 30
    ) {
        $this->httpClient = $httpClient;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
        $this->httpClient->setTimeout($requestTimeout);
    }

    /**
     * @inheritdoc
     */
    public function getContent($version, $edition, $locale)
    {
        $result = false;

        try {
            $result = $this->retrieveContent($version, $edition, $locale);
            if (!$result) {
                $result = $this->retrieveContent($version, $edition, Locale::DEFAULT_SYSTEM_LOCALE);
                if (!$result) {
                    $result = $this->retrieveContent($version, '', 'default');
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning('Failed to retrieve the release notification content.', ['exception' => $e]);
        }

        return $result;
    }

    /**
     * Retrieve content from given url
     *
     * @param string $version
     * @param string $edition
     * @param string $locale
     * @return bool|string
     */
    private function retrieveContent($version, $edition, $locale)
    {
        $url = $this->urlBuilder->getUrl($version, $edition, $locale);
        return empty($url) ? false : $this->getResponse($url);
    }

    /**
     * Returns the response body from the HTTP client
     *
     * @param string $url
     * @return string
     */
    private function getResponse($url)
    {
        $this->httpClient->get($url);
        $responseBody = $this->httpClient->getBody();

        if ($this->httpClient->getStatus() === 200 && !empty($responseBody)) {
            return $responseBody;
        }

        return false;
    }
}
