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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PageCache\Model\App\FrontController;

use Magento\PageCache\Helper\Data;

/**
 * Class HeadPlugin
 */
class HeaderPlugin
{
    /**
     * @var \Magento\Core\Model\Layout
     */
    protected $layout;

    /**
     * @var \Magento\App\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\PageCache\Model\Version
     */
    protected $version;

    /**
     * Constructor
     *
     * @param \Magento\Core\Model\Layout $layout
     * @param \Magento\App\ConfigInterface $config
     * @param \Magento\PageCache\Model\Version $version
     */
    public function __construct(
        \Magento\Core\Model\Layout $layout,
        \Magento\App\ConfigInterface $config,
        \Magento\PageCache\Model\Version $version
    ) {
        $this->layout = $layout;
        $this->config = $config;
        $this->version = $version;
    }

    /**
     * Modify response after dispatch
     *
     * @param \Magento\App\Response\Http $response
     * @return \Magento\App\Response\Http
     */
    public function afterDispatch(\Magento\App\Response\Http $response)
    {
        if ($this->layout->isPrivate()) {
            $this->setPrivateHeaders($response);
            return $response;
        }
        if ($this->layout->isCacheable()) {
            $this->setPublicHeaders($response);
        } else {
            $this->setNocacheHeaders($response);
        }
        $this->version->process();
        return $response;
    }

    /**
     * @param \Magento\App\Response\Http $response
     */
    protected function setPublicHeaders(\Magento\App\Response\Http $response)
    {
        $maxAge = $this->config->getValue(\Magento\PageCache\Model\Config::XML_PAGECACHE_TTL);
        $response->setHeader('pragma', 'cache', true);
        $response->setHeader('cache-control', 'public, max-age=' . $maxAge . ', s-maxage=' . $maxAge, true);
        $response->setHeader('expires', gmdate('D, d M Y H:i:s T', strtotime('+' . $maxAge . ' seconds')), true);
    }

    /**
     * @param \Magento\App\Response\Http $response
     */
    protected function setNocacheHeaders(\Magento\App\Response\Http $response)
    {
        $response->setHeader('pragma', 'no-cache', true);
        $response->setHeader('cache-control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        $response->setHeader('expires', gmdate('D, d M Y H:i:s T', strtotime('-1 year')), true);
    }

    /**
     * Set header parameters for private cache
     *
     * @param \Magento\App\Response\Http $response
     */
    protected function setPrivateHeaders(\Magento\App\Response\Http $response)
    {
        $maxAge = Data::PRIVATE_MAX_AGE_CACHE;
        $response->setHeader('pragma', 'cache', true);
        $response->setHeader('cache-control', 'private, max-age=' . $maxAge, true);
        $response->setHeader('expires', gmdate('D, d M Y H:i:s T', strtotime('+' . $maxAge . ' seconds')), true);
    }
}
