<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magento\Customer\Controller\Account
{
    /** @var \Magento\Core\Helper\Data */
    protected $coreHelperData;

    /** @var \Magento\Customer\Helper\Data */
    protected $_customerHelperData;

    /** @var \Magento\Core\App\Action\FormKeyValidator */
    protected $_formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param \Magento\Core\Helper\Data $coreHelperData
     * @param \Magento\Customer\Helper\Data $customerHelperData
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerAccountServiceInterface $customerAccountService,
        \Magento\Core\Helper\Data $coreHelperData,
        \Magento\Customer\Helper\Data $customerHelperData,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
    ) {
        $this->coreHelperData = $coreHelperData;
        $this->_customerHelperData = $customerHelperData;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct(
            $context,
            $customerSession,
            $addressHelper,
            $urlFactory,
            $storeManager,
            $scopeConfig,
            $customerAccountService
        );
    }

    /**
     * Define target URL and redirect customer after logging in
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _loginPostRedirect()
    {
        $lastCustomerId = $this->_getSession()->getLastCustomerId();
        if (isset(
            $lastCustomerId
            ) && $this->_getSession()->isLoggedIn() && $lastCustomerId != $this->_getSession()->getId()
        ) {
            $this->_getSession()->unsBeforeAuthUrl()->setLastCustomerId($this->_getSession()->getId());
        }
        if (!$this->_getSession()->getBeforeAuthUrl() ||
            $this->_getSession()->getBeforeAuthUrl() == $this->_storeManager->getStore()->getBaseUrl()
        ) {
            // Set default URL to redirect customer to
            $this->_getSession()->setBeforeAuthUrl($this->_customerHelperData->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($this->_getSession()->isLoggedIn()) {
                if (!$this->_scopeConfig->isSetFlag(
                    \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
                ) {
                    $referer = $this->getRequest()->getParam(\Magento\Customer\Helper\Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        $referer = $this->coreHelperData->urlDecode($referer);
                        if ($this->_url->isOwnOriginUrl()) {
                            $this->_getSession()->setBeforeAuthUrl($referer);
                        }
                    }
                } elseif ($this->_getSession()->getAfterAuthUrl()) {
                    $this->_getSession()->setBeforeAuthUrl($this->_getSession()->getAfterAuthUrl(true));
                }
            } else {
                $this->_getSession()->setBeforeAuthUrl($this->_customerHelperData->getLoginUrl());
            }
        } elseif ($this->_getSession()->getBeforeAuthUrl() == $this->_customerHelperData->getLogoutUrl()) {
            $this->_getSession()->setBeforeAuthUrl($this->_customerHelperData->getDashboardUrl());
        } else {
            if (!$this->_getSession()->getAfterAuthUrl()) {
                $this->_getSession()->setAfterAuthUrl($this->_getSession()->getBeforeAuthUrl());
            }
            if ($this->_getSession()->isLoggedIn()) {
                $this->_getSession()->setBeforeAuthUrl($this->_getSession()->getAfterAuthUrl(true));
            }
        }
        $this->getResponse()->setRedirect($this->_getSession()->getBeforeAuthUrl(true));
    }

    /**
     * Login post action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn() || !$this->_formKeyValidator->validate($this->getRequest())) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->_customerAccountService->authenticate($login['username'], $login['password']);
                    $this->_getSession()->setCustomerDataAsLoggedIn($customer);
                    $this->_getSession()->regenerateId();
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->_customerHelperData->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed.' .
                        ' <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                    $this->messageManager->addError($message);
                    $this->_getSession()->setUsername($login['username']);
                }
                catch (AuthenticationException $e) {
                    $message = __('Invalid login or password.');
                    $this->messageManager->addError($message);
                    $this->_getSession()->setUsername($login['username']);
                } catch (\Exception $e) {
                    // PA DSS violation: this exception log can disclose customer password
                    // $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
                    $this->messageManager->addError(__('There was an error validating the login and password.'));
                }
            } else {
                $this->messageManager->addError(__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }
}
