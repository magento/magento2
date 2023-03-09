<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\Url\Plugin;

use Magento\Framework\App\View\Deployment\Version;
use Magento\Framework\Url\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Url\ConfigInterface;

/**
 * Plugin that activates signing of static file URLs with corresponding deployment version
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
     * @param ConfigInterface $config
     * @param Version $deploymentVersion
     */
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly Version $deploymentVersion
    ) {
    }

    /**
     * Append signature to rendered base URL for static view files
     *
     * @param ScopeInterface $subject
     * @param string $baseUrl
     * @param string $type
     * @param null $secure
     * @return string
     * @see ScopeInterface::getBaseUrl
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetBaseUrl(
        ScopeInterface $subject,
        $baseUrl,
        $type = UrlInterface::URL_TYPE_LINK,
        $secure = null
    ) {
        if ($type == UrlInterface::URL_TYPE_STATIC && $this->isUrlSignatureEnabled()) {
            $baseUrl .= $this->renderUrlSignature() . '/';
        }
        return $baseUrl;
    }

    /**
     * Whether signing of URLs is enabled or not
     *
     * @return bool
     */
    protected function isUrlSignatureEnabled()
    {
        return (bool)$this->config->getValue(self::XML_PATH_STATIC_FILE_SIGNATURE);
    }

    /**
     * Render URL signature from the template
     *
     * @return string
     */
    protected function renderUrlSignature()
    {
        return sprintf(self::SIGNATURE_TEMPLATE, $this->deploymentVersion->getValue());
    }
}
