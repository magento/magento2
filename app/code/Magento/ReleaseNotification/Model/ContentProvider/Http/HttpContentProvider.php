<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ReleaseNotification\Model\ContentProvider\Http;

use Magento\ReleaseNotification\Model\ContentProviderInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Setup\Module\I18n\Locale;
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
     * @var ClientInterface
     */
    private $httpClient;

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
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * HttpContentProvider constructor.
     * @param ClientInterface $httpClient
     * @param ProductMetadataInterface $productMetadata
     * @param Session $session
     * @param ResponseResolver $responseResolver
     * @param UrlBuilder $urlBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $httpClient,
        ProductMetadataInterface $productMetadata,
        Session $session,
        ResponseResolver $responseResolver,
        UrlBuilder $urlBuilder,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->productMetadata = $productMetadata;
        $this->session = $session;
        $this->responseResolver = $responseResolver;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        $result = false;

        $locale = $this->session->getUser()->getInterfaceLocale();
        $version = $this->getTargetVersion();
        $edition = $this->productMetadata->getEdition();

        try {
            $result = $this->retrieveContent($version, $edition, $locale);
            if (!$result && ($locale !== Locale::DEFAULT_SYSTEM_LOCALE)) {
                $result = $this->retrieveContent($version, $edition, Locale::DEFAULT_SYSTEM_LOCALE);
                if (!$result) {
                    $result = $this->retrieveContent($version, '', 'default');
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning(
                sprintf(
                    'Failed to retrieve the release notification content. The respose is: %s',
                    !empty($response) ? $response : 'Response body is empty.'
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
        return empty($url)
            ? false
            : $this->responseResolver->getResult($this->getResponse($url), $this->httpClient->getStatus());
    }
}
