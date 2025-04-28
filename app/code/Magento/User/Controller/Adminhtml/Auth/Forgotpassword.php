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
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Security\Model\SecurityManager;
use Magento\Backend\App\Action\Context;
use Magento\User\Model\Spi\NotificationExceptionInterface;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\Framework\Validator\EmailAddress;
use Magento\Security\Model\PasswordResetRequestEvent;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\User\Controller\Adminhtml\Auth;
use Magento\Backend\Helper\Data;
use Magento\User\Model\Spi\NotificatorInterface;

/**
 * Initiate forgot-password process.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Forgotpassword extends Auth implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var SecurityManager
     */
    protected $securityManager;

    /**
     * @var NotificatorInterface
     */
    private $notificator;

    /**
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
     * @param Data $backendDataHelper
     * @param NotificatorInterface|null $notificator
     */
    public function __construct(
        Context $context,
        UserFactory $userFactory,
        SecurityManager $securityManager,
        ?CollectionFactory $userCollectionFactory = null,
        ?Data $backendDataHelper = null,
        ?NotificatorInterface $notificator = null
    ) {
        parent::__construct($context, $userFactory);
        $this->securityManager = $securityManager;
        $this->userCollectionFactory = $userCollectionFactory ?:
                ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->backendDataHelper = $backendDataHelper ?:
                ObjectManager::getInstance()->get(Data::class);
        $this->notificator = $notificator
            ?? ObjectManager::getInstance()->get(NotificatorInterface::class);
    }

    /**
     * Forgot administrator password action
     *
     * @return void
     * @throws ValidateException|NotificationExceptionInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    public function execute()
    {
        $email = (string)$this->getRequest()->getParam('email');
        $params = $this->getRequest()->getParams();

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!empty($email) && !empty($params)) {
            // Validate received data to be an email address
            if (ValidatorChain::is($email, EmailAddress::class)) {
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
