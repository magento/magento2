<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use \Braintree_Result_Error;

abstract class MyCreditCards extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $config;

    /**
     * @var \Magento\Braintree\Model\Vault
     */
    protected $vault;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param \Magento\Braintree\Model\Vault $vault
     * @param \Magento\Braintree\Model\Config\Cc $config
     * @param @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Braintree\Model\Vault $vault,
        \Magento\Braintree\Model\Config\Cc $config,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->vault = $vault;
        $this->config = $config;
        $this->customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();
        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        if (!$this->config->useVault()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('noRoute');
            return $resultRedirect;
        }

        return parent::dispatch($request);
    }

    /**
     * If token exists
     *
     * @return boolean
     */
    protected function hasToken()
    {
        return $this->getRequest()->getParam('token');
    }
}
