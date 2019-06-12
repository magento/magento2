<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Auth;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Security\Model\SecurityManager;
<<<<<<< HEAD
use Magento\Framework\App\ObjectManager;
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Backend\App\Action\Context;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\Framework\Validator\EmailAddress;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\User\Controller\Adminhtml\Auth;
use Magento\Backend\Helper\Data;
<<<<<<< HEAD

class Forgotpassword extends Auth
=======
use Magento\User\Model\Spi\NotificatorInterface;

/**
 * Initiate forgot-password process.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Forgotpassword extends Auth implements HttpGetActionInterface, HttpPostActionInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
{
    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
<<<<<<< HEAD
=======
     * @var NotificatorInterface
     */
    private $notificator;

    /**
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * User model factory
     *
     * @var CollectionFactory
     */
    private $userCollectionFactory;

    /**
     * @var Data
     */
    private $backendDataHelper;

    /**
     * @param Context $context
     * @param UserFactory $userFactory
     * @param SecurityManager $securityManager
     * @param CollectionFactory $userCollectionFactory
<<<<<<< HEAD
=======
     * @param Data $backendDataHelper
     * @param NotificatorInterface|null $notificator
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function __construct(
        Context $context,
        UserFactory $userFactory,
        SecurityManager $securityManager,
        CollectionFactory $userCollectionFactory = null,
<<<<<<< HEAD
        Data $backendDataHelper = null
=======
        Data $backendDataHelper = null,
        ?NotificatorInterface $notificator = null
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    ) {
        parent::__construct($context, $userFactory);
        $this->securityManager = $securityManager;
        $this->userCollectionFactory = $userCollectionFactory ?:
                ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->backendDataHelper = $backendDataHelper ?:
                ObjectManager::getInstance()->get(Data::class);
<<<<<<< HEAD
=======
        $this->notificator = $notificator
            ?? ObjectManager::getInstance()->get(NotificatorInterface::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * Forgot administrator password action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $email = (string)$this->getRequest()->getParam('email');
        $params = $this->getRequest()->getParams();

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!empty($email) && !empty($params)) {
            // Validate received data to be an email address
            if (\Zend_Validate::is($email, EmailAddress::class)) {
                try {
                    $this->securityManager->performSecurityCheck(
                        PasswordResetRequestEvent::ADMIN_PASSWORD_RESET_REQUEST,
                        $email
                    );
                } catch (SecurityViolationException $exception) {
                    $this->messageManager->addErrorMessage($exception->getMessage());
                    return $resultRedirect->setPath('admin');
                }
                /** @var $collection \Magento\User\Model\ResourceModel\User\Collection */
                $collection = $this->userCollectionFactory->create();
                $collection->addFieldToFilter('email', $email);
                $collection->load(false);

                try {
                    if ($collection->getSize() > 0) {
                        foreach ($collection as $item) {
                            /** @var \Magento\User\Model\User $user */
                            $user = $this->_userFactory->create()->load($item->getId());
                            if ($user->getId()) {
                                $newPassResetToken = $this->backendDataHelper->generateResetPasswordLinkToken();
                                $user->changeResetPasswordLinkToken($newPassResetToken);
                                $user->save();
                                $this->notificator->sendForgotPassword($user);
                            }
                            break;
                        }
                    }
                } catch (\Exception $exception) {
                    $this->messageManager->addExceptionMessage(
                        $exception,
                        __('We\'re unable to send the password reset email.')
                    );
                    return $resultRedirect->setPath('admin');
                }
                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(__('We\'ll email you a link to reset your password.'));
                // @codingStandardsIgnoreEnd
                $this->getResponse()->setRedirect(
                    $this->backendDataHelper->getHomePageUrl()
                );
                return;
            } else {
                $this->messageManager->addError(__('Please correct this email address:'));
            }
        } elseif (!empty($params)) {
            $this->messageManager->addError(__('Please enter an email address.'));
        }
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
