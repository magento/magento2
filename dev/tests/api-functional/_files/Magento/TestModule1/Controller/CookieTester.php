<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestModule1\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

/**
 * Controller for testing the CookieManager.
 *
 */
class CookieTester extends \Magento\Framework\App\Action\Action
{
    /** @var PhpCookieManager */
    protected $cookieManager;

    /** @var  CookieMetadataFactory */
    protected $cookieMetadataFactory;

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
        parent::__construct($context);
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
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->getRequest()->isDispatched()) {
            parent::dispatch($request);
        }

        $result = parent::dispatch($request);
        return $result;
    }
}
