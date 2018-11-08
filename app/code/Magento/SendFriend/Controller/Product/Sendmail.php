<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SendFriend\Controller\Product;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\DefaultModel;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class Sendmail. Represents request flow logic of 'sendmail' feature
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Sendmail extends \Magento\SendFriend\Controller\Product implements HttpPostActionInterface
{
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var Data
     */
    private $captchaHelper;

    /**
     * @var CaptchaStringResolver
     */
    private $captchaStringResolver;

    /**
     * @var UserContextInterface
     */
    private $currentUser;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Sendmail class construct
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\SendFriend\Model\SendFriend $sendFriend
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param Data|null $captchaHelper
     * @param CaptchaStringResolver|null $captchaStringResolver
     * @param UserContextInterface|null $currentUser
     * @param CustomerRepositoryInterface|null $customerRepository
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\SendFriend\Model\SendFriend $sendFriend,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Session $catalogSession,
        ?Data $captchaHelper = null,
        ?CaptchaStringResolver $captchaStringResolver = null,
        ?UserContextInterface $currentUser = null,
        ?CustomerRepositoryInterface $customerRepository = null
    ) {
        parent::__construct($context, $coreRegistry, $formKeyValidator, $sendFriend, $productRepository);
        $this->categoryRepository = $categoryRepository;
        $this->catalogSession = $catalogSession;
        $this->captchaHelper = $captchaHelper ?: ObjectManager::getInstance()->create(Data::class);
        $this->captchaStringResolver = $captchaStringResolver ?:
            ObjectManager::getInstance()->create(CaptchaStringResolver::class);
        $this->currentUser = $currentUser ?: ObjectManager::getInstance()->get(UserContextInterface::class);
        $this->customerRepository = $customerRepository ?:
            ObjectManager::getInstance()->create(CustomerRepositoryInterface::class);
    }

    /**
     * Send Email Post Action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $product = $this->_initProduct();
        $data = $this->getRequest()->getPostValue();

        if (!$product || !$data) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $categoryId = $this->getRequest()->getParam('cat_id', null);
        if ($categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
            } catch (NoSuchEntityException $noEntityException) {
                $category = null;
            }
            if ($category) {
                $product->setCategory($category);
                $this->_coreRegistry->register('current_category', $category);
            }
        }

        $this->sendFriend->setSender($this->getRequest()->getPost('sender'));
        $this->sendFriend->setRecipients($this->getRequest()->getPost('recipients'));
        $this->sendFriend->setProduct($product);

        try {
            $validate = $this->sendFriend->validate();

            $this->validateCaptcha();

            if ($validate === true) {
                //$this->sendFriend->send();
                $this->messageManager->addSuccess(__('The link to a friend was sent.'));
                $url = $product->getProductUrl();
                $resultRedirect->setUrl($this->_redirect->success($url));
                return $resultRedirect;
            } else {
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                } else {
                    $this->messageManager->addError(__('We found some problems with the data.'));
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Some emails were not sent.'));
        }

        // save form data
        $this->catalogSession->setSendfriendFormData($data);

        $url = $this->_url->getUrl('sendfriend/product/send', ['_current' => true]);
        $resultRedirect->setUrl($this->_redirect->error($url));
        return $resultRedirect;
    }

    /**
     * Method validates captcha, if it's enabled for target form
     *
     * @throws LocalizedException
     */
    private function validateCaptcha() : void
    {
        $captchaTargetFormName = 'product_sendtofriend_form';
        /** @var DefaultModel $captchaModel */
        $captchaModel = $this->captchaHelper->getCaptcha($captchaTargetFormName);

        if ($captchaModel->isRequired()) {
            $word = $this->captchaStringResolver->resolve(
                $this->getRequest(),
                $captchaTargetFormName
            );

            $isCorrectCaptcha = $captchaModel->isCorrect($word);

            if (!$isCorrectCaptcha) {
                $this->logCaptchaAttempt($captchaModel);
                throw new LocalizedException(__('Incorrect CAPTCHA'));
            }
        }

        $this->logCaptchaAttempt($captchaModel);
    }

    /**
     * Log captcha attempts
     *
     * @param DefaultModel $captchaModel
     */
    private function logCaptchaAttempt(DefaultModel $captchaModel) : void
    {
        $email = '';

        if ($this->currentUser->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $email = $this->customerRepository->getById($this->currentUser->getUserId())->getEmail();
        }

        $captchaModel->logAttempt($email);
    }
}
