<?php
/**
 * Abstract redirect/forward action class
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Class \Magento\Framework\App\Action\AbstractAction
 *
 * @since 2.0.0
 */
abstract class AbstractAction implements \Magento\Framework\App\ActionInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     * @since 2.0.0
     */
    protected $_response;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     * @since 2.0.0
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     * @since 2.0.0
     */
    protected $resultFactory;

    /**
     * @param Context $context
     * @since 2.0.0
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
     * @since 2.0.0
     */
    abstract public function dispatch(RequestInterface $request);

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve response object
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @since 2.0.0
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
