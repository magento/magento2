<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\Controller\ResultFactory;

/**
 * Constructor modification point for Magento\Framework\App\Action.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 *
 * @api
 * @since 2.0.0
 */
class Context implements \Magento\Framework\ObjectManager\ContextInterface
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
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_url;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     * @since 2.0.0
     */
    protected $_redirect;

    /**
     * @var \Magento\Framework\App\ActionFlag
     * @since 2.0.0
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Framework\App\ViewInterface
     * @since 2.0.0
     */
    protected $_view;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

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
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\App\ViewInterface $view
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        ResultFactory $resultFactory
    ) {
        $this->_request = $request;
        $this->_response = $response;
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_redirect = $redirect;
        $this->_actionFlag = $actionFlag;
        $this->_view = $view;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return \Magento\Framework\App\ActionFlag
     * @since 2.0.0
     */
    public function getActionFlag()
    {
        return $this->_actionFlag;
    }

    /**
     * @return \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    public function getEventManager()
    {
        return $this->_eventManager;
    }

    /**
     * @return \Magento\Framework\App\ViewInterface
     * @since 2.0.0
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * @return \Magento\Framework\App\Response\RedirectInterface
     * @since 2.0.0
     */
    public function getRedirect()
    {
        return $this->_redirect;
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     * @since 2.0.0
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @return \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    public function getMessageManager()
    {
        return $this->messageManager;
    }

    /**
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     * @since 2.0.0
     */
    public function getResultRedirectFactory()
    {
        return $this->resultRedirectFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultFactory
     * @since 2.0.0
     */
    public function getResultFactory()
    {
        return $this->resultFactory;
    }
}
