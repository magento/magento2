<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Exception;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Model\DefaultModel as CaptchaModel;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Session\Generic as WishlistSession;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Framework\View\Result\Layout as ResultLayout;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Config;

/**
 * Class Send Email Wishlist Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Send extends AbstractIndex implements Action\HttpPostActionInterface
{
    /**
     * @var View
     */
    protected $_customerHelperView;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var Config
     */
    protected $_wishlistConfig;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @param Action\Context $context
     * @param Validator $formKeyValidator
     * @param Session $customerSession
     * @param WishlistProviderInterface $wishlistProvider
     * @param Config $wishlistConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param View $customerHelperView
     * @param WishlistSession $wishlistSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CaptchaHelper|null $captchaHelper
     * @param CaptchaStringResolver|null $captchaStringResolver
     * @param Escaper|null $escaper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Action\Context $context,
        Validator $formKeyValidator,
        Session $customerSession,
        protected readonly WishlistProviderInterface $wishlistProvider,
        Config $wishlistConfig,
        TransportBuilder $transportBuilder,
        protected readonly StateInterface $inlineTranslation,
        View $customerHelperView,
        protected readonly WishlistSession $wishlistSession,
        protected readonly ScopeConfigInterface $scopeConfig,
        protected readonly StoreManagerInterface $storeManager,
        private ?CaptchaHelper $captchaHelper = null,
        private ?CaptchaStringResolver $captchaStringResolver = null,
        private ?Escaper $escaper = null
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->_customerSession = $customerSession;
        $this->_wishlistConfig = $wishlistConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_customerHelperView = $customerHelperView;
        $this->captchaHelper = $captchaHelper ?: ObjectManager::getInstance()->get(CaptchaHelper::class);
        $this->captchaStringResolver = $captchaStringResolver ?: ObjectManager::getInstance()->get(
            CaptchaStringResolver::class
        );
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(
            Escaper::class
        );
        parent::__construct($context);
    }

    /**
     * Share wishlist
     *
     * @return Redirect
     * @throws NotFoundException|ValidateException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $captchaForName = 'share_wishlist_form';
        /** @var CaptchaModel $captchaModel */
        $captchaModel = $this->captchaHelper->getCaptcha($captchaForName);

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $isCorrectCaptcha = $this->validateCaptcha($captchaModel, $captchaForName);

        $this->logCaptchaAttempt($captchaModel);

        if (!$isCorrectCaptcha) {
            $this->messageManager->addErrorMessage(__('Incorrect CAPTCHA'));
            $resultRedirect->setPath('*/*/share');
            return $resultRedirect;
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }

        $sharingLimit = $this->_wishlistConfig->getSharingEmailLimit();
        $textLimit = $this->_wishlistConfig->getSharingTextLimit();
        $emailsLeft = $sharingLimit - $wishlist->getShared();

        $emails = $this->getRequest()->getPost('emails');
        $emails = empty($emails) ? $emails : explode(',', $emails);

        $error = false;
        $message = (string)$this->getRequest()->getPost('message');
        if (strlen($message) > $textLimit) {
            $error = __('Message length must not exceed %1 symbols', $textLimit);
        } else {
            $message = nl2br((string) $this->escaper->escapeHtml($message));
            if (empty($emails)) {
                $error = __('Please enter an email address.');
            } else {
                if (count($emails) > $emailsLeft) {
                    $error = __('Maximum of %1 emails can be sent.', $emailsLeft);
                } else {
                    foreach ($emails as $index => $email) {
                        $email = $email !== null ? trim($email) : '';
                        if (!ValidatorChain::is($email, EmailAddress::class)) {
                            $error = __('Please enter a valid email address.');
                            break;
                        }
                        $emails[$index] = $email;
                    }
                }
            }
        }

        if ($error) {
            $this->messageManager->addErrorMessage($error);
            $this->wishlistSession->setSharingForm($this->getRequest()->getPostValue());
            $resultRedirect->setPath('*/*/share');
            return $resultRedirect;
        }
        /** @var ResultLayout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $this->addLayoutHandles($resultLayout);
        $this->inlineTranslation->suspend();

        $sent = 0;

        try {
            $customer = $this->_customerSession->getCustomerDataObject();
            $customerName = $this->_customerHelperView->getCustomerName($customer);

            $message .= $this->getRssLink($wishlist->getId(), $resultLayout);
            $emails = array_unique($emails);
            $sharingCode = $wishlist->getSharingCode();

            try {
                foreach ($emails as $email) {
                    $transport = $this->_transportBuilder->setTemplateIdentifier(
                        $this->scopeConfig->getValue(
                            'wishlist/email/email_template',
                            ScopeInterface::SCOPE_STORE
                        )
                    )->setTemplateOptions(
                        [
                            'area' => Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore()->getStoreId(),
                        ]
                    )->setTemplateVars(
                        [
                            'customer' => $customer,
                            'customerName' => $customerName,
                            'salable' => $wishlist->isSalable() ? 'yes' : '',
                            'items' => $this->getWishlistItems($resultLayout),
                            'viewOnSiteLink' => $this->_url->getUrl('*/shared/index', ['code' => $sharingCode]),
                            'message' => $message,
                            'store' => $this->storeManager->getStore(),
                        ]
                    )->setFrom(
                        $this->scopeConfig->getValue(
                            'wishlist/email/email_identity',
                            ScopeInterface::SCOPE_STORE
                        )
                    )->addTo(
                        $email
                    )->getTransport();

                    $transport->sendMessage();

                    $sent++;
                }
            } catch (Exception $e) {
                $wishlist->setShared($wishlist->getShared() + $sent);
                $wishlist->save();
                throw $e;
            }
            $wishlist->setShared($wishlist->getShared() + $sent);
            $wishlist->save();

            $this->inlineTranslation->resume();

            $this->_eventManager->dispatch('wishlist_share', ['wishlist' => $wishlist]);
            $this->messageManager->addSuccessMessage(__('Your wish list has been shared.'));
            $resultRedirect->setPath('*/*', ['wishlist_id' => $wishlist->getId()]);
            return $resultRedirect;
        } catch (Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->wishlistSession->setSharingForm($this->getRequest()->getPostValue());
            $resultRedirect->setPath('*/*/share');
            return $resultRedirect;
        }
    }

    /**
     * Prepare to load additional email blocks
     *
     * Add 'wishlist_email_rss' layout handle.
     * Add 'wishlist_email_items' layout handle.
     *
     * @param ResultLayout $resultLayout
     * @return void
     */
    protected function addLayoutHandles(ResultLayout $resultLayout)
    {
        if ($this->getRequest()->getParam('rss_url')) {
            $resultLayout->addHandle('wishlist_email_rss');
        }
        $resultLayout->addHandle('wishlist_email_items');
    }

    /**
     * Retrieve RSS link content (html)
     *
     * @param int $wishlistId
     * @param ResultLayout $resultLayout
     */
    protected function getRssLink($wishlistId, ResultLayout $resultLayout)
    {
        if ($this->getRequest()->getParam('rss_url')) {
            return $resultLayout->getLayout()
                ->getBlock('wishlist.email.rss')
                ->setWishlistId($wishlistId)
                ->toHtml();
        }
    }

    /**
     * Retrieve wishlist items content (html)
     *
     * @param ResultLayout $resultLayout
     * @return string
     */
    protected function getWishlistItems(ResultLayout $resultLayout)
    {
        return $resultLayout->getLayout()
            ->getBlock('wishlist.email.items')
            ->toHtml();
    }

    /**
     * Log customer action attempts
     *
     * @param CaptchaModel $captchaModel
     * @return void
     */
    private function logCaptchaAttempt(CaptchaModel $captchaModel): void
    {
        /** @var  Customer $customer */
        $customer = $this->_customerSession->getCustomer();
        $email = '';

        if ($customer->getId()) {
            $email = $customer->getEmail();
        }

        $captchaModel->logAttempt($email);
    }

    /**
     * Captcha validate logic
     *
     * @param CaptchaModel $captchaModel
     * @param string $captchaFormName
     * @return bool
     */
    private function validateCaptcha(CaptchaModel $captchaModel, string $captchaFormName) : bool
    {
        if ($captchaModel->isRequired()) {
            $word = $this->captchaStringResolver->resolve(
                $this->getRequest(),
                $captchaFormName
            );

            if (!$captchaModel->isCorrect($word)) {
                return false;
            }
        }

        return true;
    }
}
