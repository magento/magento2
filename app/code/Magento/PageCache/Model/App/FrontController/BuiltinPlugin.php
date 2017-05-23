<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\App\FrontController;

use Magento\Framework\App\Response\Http as ResponseHttp;

/**
 * Plugin for processing builtin cache
 */
class BuiltinPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\PageCache\Version
     */
    protected $version;

    /**
     * @var \Magento\Framework\App\PageCache\Kernel
     */
    protected $kernel;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * Constructor
     *
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\PageCache\Version $version
     * @param \Magento\Framework\App\PageCache\Kernel $kernel
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\PageCache\Version $version,
        \Magento\Framework\App\PageCache\Kernel $kernel,
        \Magento\Framework\App\State $state
    ) {
        $this->config = $config;
        $this->version = $version;
        $this->kernel = $kernel;
        $this->state = $state;
    }

    /**
     * @param \Magento\Framework\App\FrontControllerInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\Response\Http
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\FrontControllerInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$this->config->isEnabled() || $this->config->getType() != \Magento\PageCache\Model\Config::BUILT_IN) {
            return $proceed($request);
        }
        $this->version->process();
        $result = $this->kernel->load();
        if ($result === false) {
            $result = $proceed($request);
            if ($result instanceof ResponseHttp) {
                $this->addDebugHeaders($result);
                $this->kernel->process($result);
            }
        } else {
            $this->addDebugHeader($result, 'X-Magento-Cache-Debug', 'HIT', true);
        }
        return $result;
    }

    /**
     * Set cache control
     *
     * @param ResponseHttp $result
     * @return ResponseHttp
     */
    protected function addDebugHeaders(ResponseHttp $result)
    {
        $cacheControlHeader = $result->getHeader('Cache-Control');
        if ($cacheControlHeader instanceof \Zend\Http\Header\HeaderInterface) {
            $this->addDebugHeader($result, 'X-Magento-Cache-Control', $cacheControlHeader->getFieldValue());
        }
        $this->addDebugHeader($result, 'X-Magento-Cache-Debug', 'MISS', true);
        return $result;
    }

    /**
     * Add additional header for debug purpose
     *
     * @param ResponseHttp $response
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @return void
     */
    protected function addDebugHeader(ResponseHttp $response, $name, $value, $replace = false)
    {
        if ($this->state->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
            $response->setHeader($name, $value, $replace);
        }
    }
}
