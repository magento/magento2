<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SendFriend\Controller\Product;

use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Captcha\Model\DefaultModel as CaptchaModel;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Session as CustomerSession;

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
     * @var CaptchaHelper
     */
    private $captchaHelper;

    /**
     * @var CaptchaStringResolver
     */
    private $captchaStringResolver;

    /**
     * @var CustomerSession
     */
    private $customerSession;

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
     * @param CaptchaHelper|null $captchaHelper
     * @param CaptchaStringResolver|null $captchaStringResolver
     * @param CustomerSession|null $customerSession
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
        ?CaptchaHelper $captchaHelper = null,
        ?CaptchaStringResolver $captchaStringResolver = null,
        ?CustomerSession $customerSession = null
    ) {
        parent::__construct($context, $coreRegistry, $formKeyValidator, $sendFriend, $productRepository);
        $this->categoryRepository = $categoryRepository;
        $this->catalogSession = $catalogSession;
        $this->captchaHelper = $captchaHelper ?: ObjectManager::getInstance()->get(CaptchaHelper::class);
        $this->captchaStringResolver = $captchaStringResolver ?:
            ObjectManager::getInstance()->get(CaptchaStringResolver::class);
        $this->customerSession = $customerSession ?: ObjectManager::getInstance()->get(CustomerSession::class);
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
        $captchaTargetFormId = 'product_sendtofriend_form';
        /** @var CaptchaModel $captchaModel */
        $captchaModel = $this->captchaHelper->getCaptcha($captchaTargetFormId);

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setPath('sendfriend/product/send', ['_current' => true]);
            return $resultRedirect;
        }

        $isCorrectCaptcha = $this->validateCaptcha($captchaModel, $captchaTargetFormId);

        $this->logCaptchaAttempt($captchaModel, $captchaTargetFormId);

        if (!$isCorrectCaptcha) {
            $this->messageManager->addErrorMessage(__('Incorrect CAPTCHA'));
            $resultRedirect->setRefererUrl();

            return $resultRedirect;
        }

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
            if ($validate === true) {
                $this->sendFriend->send();
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
     * Validate the given captcha word
     *
     * @param CaptchaModel $captchaModel
     * @param string $captchaTargetFormName
     * @return bool
     */
    private function validateCaptcha(CaptchaModel $captchaModel, $captchaTargetFormName) : bool
    {
        if ($captchaModel->isRequired()) {
            $word = $this->captchaStringResolver->resolve(
                $this->getRequest(),
                $captchaTargetFormName
            );

            if (!$captchaModel->isCorrect($word)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Logs a try to pass captcha validation
     *
     * @param CaptchaModel $captchaModel
     */
    private function logCaptchaAttempt(CaptchaModel $captchaModel): void
    {
        /** @var Customer $customer */
        $customer = $this->customerSession->getCustomer();
        $email = '';

        if ($customer->getId()) {
            $email = $customer->getEmail();
        }

        $captchaModel->logAttempt($email);
    }
}
