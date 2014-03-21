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

/**
 * Varnish for processing builtin cache
 */
class VarnishPlugin
{
    /**
     * @var \Magento\App\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\App\PageCache\Version
     */
    protected $version;

    /**
     * @var \Magento\App\State
     */
    protected $state;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\App\PageCache\Version $version
     * @param \Magento\App\State $state
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\App\PageCache\Version $version,
        \Magento\App\State $state
    ) {
        $this->config = $config;
        $this->version = $version;
        $this->state = $state;
    }

    /**
     * @param \Magento\App\FrontControllerInterface $subject
     * @param callable $proceed
     * @param \Magento\App\RequestInterface $request
     * @return false|\Magento\App\Response\Http
     */
    public function aroundDispatch(
        \Magento\App\FrontControllerInterface $subject,
        \Closure $proceed,
        \Magento\App\RequestInterface $request
    ) {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
            $this->version->process();
            $response = $proceed($request);
            if ($this->state->getMode() == \Magento\App\State::MODE_DEVELOPER) {
                $response->setHeader('X-Magento-Debug', 1);
            }
        } else {
            $response = $proceed($request);
        }
        return $response;
    }
}
