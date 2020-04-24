<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Abstract redirect/forward action class
 *
 * @deprecated Use \Magento\Framework\App\ActionInterface
 * @see https://community.magento.com/t5/Magento-DevBlog/Decomposition-of-Magento-Controllers/ba-p/430883
 */
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
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->_request = $context->getRequest();
        $this->_response = $context->getResponse();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->resultFactory = $context->getResultFactory();
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    abstract public function dispatch(RequestInterface $request);

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     * @deprecated This method should not be used anymore. Inject `RequestInterface` into constructor instead
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve response object
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @deprecated This method should not be used anymore. Inject `ResponseInterface` into constructor instead
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
