<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule1\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

/**
 * Controller for testing the CookieManager.
 *
 */
abstract class CookieTester implements \Magento\Framework\App\ActionInterface
{
    /** @var PhpCookieManager */
    protected $cookieManager;

    /** @var  CookieMetadataFactory */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param PhpCookieManager $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFacory = $cookieMetadataFactory;
        $this->_response = $context->getResponse();
    }

    /**
     * Retrieve cookie metadata factory
     */
    protected function getCookieMetadataFactory()
    {
        return $this->cookieMetadataFacory;
    }

    /**
     * Retrieve cookie metadata factory
     */
    protected function getCookieManager()
    {
        return $this->cookieManager;
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    abstract protected function execute(RequestInterface $request);

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $result = $this->execute($request);
        return $result ? $result : $this->_response;
    }
}
