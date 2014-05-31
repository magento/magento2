<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Theme\Model\Url\Plugin;

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
     * @var \Magento\Framework\View\Url\ConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\App\View\Deployment\Version
     */
    private $deploymentVersion;

    /**
     * @param \Magento\Framework\View\Url\ConfigInterface $config
     * @param \Magento\Framework\App\View\Deployment\Version $deploymentVersion
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
     * @param callable $proceed
     * @param string $type
     * @param null $secure
     * @return string
     * @see \Magento\Framework\Url\ScopeInterface::getBaseUrl()
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetBaseUrl(
        \Magento\Framework\Url\ScopeInterface $subject,
        \Closure $proceed,
        $type = \Magento\Framework\UrlInterface::URL_TYPE_LINK,
        $secure = null
    ) {
        $baseUrl = $proceed($type, $secure);
        if ($type == \Magento\Framework\UrlInterface::URL_TYPE_STATIC && $this->isUrlSignatureEnabled()) {
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
