<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Model\AuthenticationStateInterface;
use Magento\Wishlist\Model\DataSerializer;

/**
 * Wishlist plugin before dispatch
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Plugin
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var AuthenticationStateInterface
     */
    protected $authenticationState;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var RedirectInterface
     */
    protected $redirector;

    /**
     * @var ManagerInterface
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
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @param CustomerSession $customerSession
     * @param AuthenticationStateInterface $authenticationState
     * @param ScopeConfigInterface $config
     * @param RedirectInterface $redirector
     * @param ManagerInterface $messageManager
     * @param DataSerializer $dataSerializer
     * @param FormKey $formKey
     * @param Validator $formKeyValidator
     */
    public function __construct(
        CustomerSession $customerSession,
        AuthenticationStateInterface $authenticationState,
        ScopeConfigInterface $config,
        RedirectInterface $redirector,
        ManagerInterface $messageManager,
        DataSerializer $dataSerializer,
        FormKey $formKey,
        Validator $formKeyValidator
    ) {
        $this->customerSession = $customerSession;
        $this->authenticationState = $authenticationState;
        $this->config = $config;
        $this->redirector = $redirector;
        $this->messageManager = $messageManager;
        $this->dataSerializer = $dataSerializer;
        $this->formKey = $formKey;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Perform customer authentication and wishlist feature state checks
     *
     * @param ActionInterface $subject
     * @param RequestInterface $request
     * @return void
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeDispatch(ActionInterface $subject, RequestInterface $request)
    {
        if ($this->authenticationState->isEnabled() && !$this->customerSession->authenticate()) {
            $subject->getActionFlag()->set('', 'no-dispatch', true);
            if (!$this->customerSession->getBeforeWishlistUrl()) {
                $this->customerSession->setBeforeWishlistUrl($this->redirector->getRefererUrl());
            }
            $data = $request->getParams();
            unset($data['login']);
            if (!($subject instanceof HttpPostActionInterface) || $this->formKeyValidator->validate($request)) {
                $this->customerSession->setBeforeWishlistRequest($data);
                $this->customerSession->setBeforeRequestParams($this->customerSession->getBeforeWishlistRequest());
                $this->customerSession->setBeforeModuleName('wishlist');
                $this->customerSession->setBeforeControllerName('index');
                $this->customerSession->setBeforeAction($request->getActionName());
            }

            if ($request->getActionName() === 'add') {
                $this->messageManager->addErrorMessage(__('You must login or register to add items to your wishlist.'));
            }
        } elseif ($this->customerSession->authenticate()) {
            if ($this->customerSession->getBeforeWishlistRequest()) {
                $data = $this->customerSession->getBeforeWishlistRequest();
                // Bypass CSRF validation as the data comes from a request that was validated
                $data['form_key'] = $this->formKey->getFormKey();
                $request->clearParams();
                $request->setParams($data);
                $this->customerSession->unsBeforeWishlistRequest();
            } elseif ($request->getParam('token')) {
                // check if the token is valid and retrieve the data
                $data = $this->dataSerializer->unserialize($request->getParam('token'));
                // Bypass CSRF validation if the token is valid
                if ($data) {
                    $data['form_key'] = $this->formKey->getFormKey();
                    $request->clearParams();
                    $request->setParams($data);
                }
            }
        }
        if (!$this->config->isSetFlag('wishlist/general/active', ScopeInterface::SCOPE_STORES)) {
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
