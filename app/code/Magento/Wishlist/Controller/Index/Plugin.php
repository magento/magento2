<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Model\DataSerializer;

/**
 * Wishlist plugin before dispatch
 */
class Plugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Wishlist\Model\AuthenticationStateInterface
     */
    protected $authenticationState;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirector;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var DataSerializer
     */
    private $dataSerializer;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @param CustomerSession $customerSession
     * @param \Magento\Wishlist\Model\AuthenticationStateInterface $authenticationState
     * @param ScopeConfigInterface $config
     * @param RedirectInterface $redirector
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param DataSerializer $dataSerializer
     * @param FormKey $formKey
     */
    public function __construct(
        CustomerSession $customerSession,
        \Magento\Wishlist\Model\AuthenticationStateInterface $authenticationState,
        ScopeConfigInterface $config,
        RedirectInterface $redirector,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        DataSerializer $dataSerializer,
        FormKey $formKey
    ) {
        $this->customerSession = $customerSession;
        $this->authenticationState = $authenticationState;
        $this->config = $config;
        $this->redirector = $redirector;
        $this->messageManager = $messageManager;
        $this->dataSerializer = $dataSerializer;
        $this->formKey = $formKey;
    }

    /**
     * Perform customer authentication and wishlist feature state checks
     *
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param RequestInterface $request
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeDispatch(\Magento\Framework\App\ActionInterface $subject, RequestInterface $request)
    {
        if ($this->authenticationState->isEnabled() && !$this->customerSession->authenticate()) {
            $subject->getActionFlag()->set('', 'no-dispatch', true);
            if (!$this->customerSession->getBeforeWishlistUrl()) {
                $this->customerSession->setBeforeWishlistUrl($this->redirector->getRefererUrl());
            }
            $data = $request->getParams();
            unset($data['login']);
            $this->customerSession->setBeforeWishlistRequest($data);
            $this->customerSession->setBeforeRequestParams($this->customerSession->getBeforeWishlistRequest());
            $this->customerSession->setBeforeModuleName('wishlist');
            $this->customerSession->setBeforeControllerName('index');
            $this->customerSession->setBeforeAction('add');

            if ($request->getActionName() === 'add') {
                $this->messageManager->addErrorMessage(__('You must login or register to add items to your wishlist.'));
            }
        } elseif ($this->customerSession->authenticate()) {
            if ($this->customerSession->getBeforeWishlistRequest()) {
                $request->setParams($this->customerSession->getBeforeWishlistRequest());
                $this->customerSession->unsBeforeWishlistRequest();
            } elseif ($request->getParam('token')) {
                // check if the token is valid and retrieve the data
                $data = $this->dataSerializer->unserialize($request->getParam('token'));
                // Bypass CSRF validation if the token is valid
                if ($data) {
                    $data['form_key'] = $this->formKey->getFormKey();
                    $request->setParams($data);
                }
            }
        }
        if (!$this->config->isSetFlag('wishlist/general/active', ScopeInterface::SCOPE_STORES)) {
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
