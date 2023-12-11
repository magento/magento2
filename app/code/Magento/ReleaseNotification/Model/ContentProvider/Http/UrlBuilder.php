<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Model\ContentProvider\Http;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Builder to build Url to retrieve the notification content.
 *
 * @deprecated Starting from Magento OS 2.4.7 Magento_ReleaseNotification module is deprecated
 * in favor of aMagento OS 2.4.7
 * @see Current in-product messaging mechanism
 */
class UrlBuilder
{
    /**
     * Path to the configuration value which contains an URL that provides the release notification data.
     *
     * @var string
     */
    private static $notificationContentUrlConfigPath = 'system/release_notification/content_url';

    /**
     * Path to the configuration value indicates if use https in notification content request.
     *
     * @var string
     */
    private static $useHttpsFlagConfigPath = 'system/release_notification/use_https';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Builds the URL to request the release notification content data based on passed parameters.
     *
     * @param string $version
     * @param string $edition
     * @param string $locale
     * @return string
     */
    public function getUrl($version, $edition, $locale)
    {
        $scheme = $this->config->isSetFlag(self::$useHttpsFlagConfigPath) ? 'https://' : 'http://';
        $baseUrl = $this->config->getValue(self::$notificationContentUrlConfigPath);
        if (empty($baseUrl)) {
            return '';
        } else {
            $url = $scheme . $baseUrl;
            $url .= empty($version) ? '' : '/' . $version;
            $url .= empty($edition) ? '' : '/' . $edition;
            $url .= empty($locale) ? '' : '/' . $locale;
            return $url . '.json';
        }
    }
}
