<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\Url\Plugin;

/**
 * Plugin that activates signing of static file URLs with corresponding deployment version
 * @since 2.0.0
 */
class Signature
{
    /**
     * XPath for configuration setting of signing static files
     */
    const XML_PATH_STATIC_FILE_SIGNATURE = 'dev/static/sign';

    /**
     * Template of signature component of URL, parametrized with the deployment version of static files
     */
    const SIGNATURE_TEMPLATE = 'version%s';

    /**
     * @var \Magento\Framework\View\Url\ConfigInterface
     * @since 2.0.0
     */
    private $config;

    /**
     * @var \Magento\Framework\App\View\Deployment\Version
     * @since 2.0.0
     */
    private $deploymentVersion;

    /**
     * @param \Magento\Framework\View\Url\ConfigInterface $config
     * @param \Magento\Framework\App\View\Deployment\Version $deploymentVersion
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Url\ConfigInterface $config,
        \Magento\Framework\App\View\Deployment\Version $deploymentVersion
    ) {
        $this->config = $config;
        $this->deploymentVersion = $deploymentVersion;
    }

    /**
     * Append signature to rendered base URL for static view files
     *
     * @param \Magento\Framework\Url\ScopeInterface $subject
     * @param string $baseUrl
     * @param string $type
     * @param null $secure
     * @return string
     * @see \Magento\Framework\Url\ScopeInterface::getBaseUrl()
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterGetBaseUrl(
        \Magento\Framework\Url\ScopeInterface $subject,
        $baseUrl,
        $type = \Magento\Framework\UrlInterface::URL_TYPE_LINK,
        $secure = null
    ) {
        if ($type == \Magento\Framework\UrlInterface::URL_TYPE_STATIC && $this->isUrlSignatureEnabled()) {
            $baseUrl .= $this->renderUrlSignature() . '/';
        }
        return $baseUrl;
    }

    /**
     * Whether signing of URLs is enabled or not
     *
     * @return bool
     * @since 2.0.0
     */
    protected function isUrlSignatureEnabled()
    {
        return (bool)$this->config->getValue(self::XML_PATH_STATIC_FILE_SIGNATURE);
    }

    /**
     * Render URL signature from the template
     *
     * @return string
     * @since 2.0.0
     */
    protected function renderUrlSignature()
    {
        return sprintf(self::SIGNATURE_TEMPLATE, $this->deploymentVersion->getValue());
    }
}
