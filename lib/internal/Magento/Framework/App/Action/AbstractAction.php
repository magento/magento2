<?php
/**
 * Abstract redirect/forward action class
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action;

abstract class AbstractAction implements \Magento\Framework\App\ActionInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_request = $request;
        $this->_response = $response;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve response object
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Redirect user to the previous or main page
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function getDefaultRedirect()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
