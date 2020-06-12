<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Controller\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\SendFriend\Controller\Product;
use Magento\SendFriend\Model\CaptchaValidator;
use Magento\SendFriend\Model\SendFriend;

/**
 * Class Sendmail. Represents request flow logic of 'sendmail' feature
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Sendmail extends Product implements HttpPostActionInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var Session
     */
    private $catalogSession;

    /**
     * @var CaptchaValidator
     */
    private $captchaValidator;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Validator $formKeyValidator
     * @param SendFriend $sendFriend
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Session $catalogSession
     * @param CaptchaValidator|null $captchaValidator
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Validator $formKeyValidator,
        SendFriend $sendFriend,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        Session $catalogSession,
        CaptchaValidator $captchaValidator
    ) {
        parent::__construct($context, $coreRegistry, $formKeyValidator, $sendFriend, $productRepository);
        $this->categoryRepository = $categoryRepository;
        $this->catalogSession = $catalogSession;
        $this->captchaValidator = $captchaValidator;
    }

    /**
     * Send Email Post Action
     *
     * @return ResultInterface
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $product = $this->_initProduct();
        $data = $this->getRequest()->getPostValue();

        if (!$product || !$data) {
            /** @var Forward $resultForward */
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

            $this->captchaValidator->validateSending($this->getRequest());

            if ($validate === true) {
                $this->sendFriend->send();
                $this->messageManager->addSuccessMessage(__('The link to a friend was sent.'));
                $url = $product->getProductUrl();
                $resultRedirect->setUrl($this->_redirect->success($url));
                return $resultRedirect;
            }

            if (is_array($validate)) {
                foreach ($validate as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
            } else {
                $this->messageManager->addErrorMessage(__('We found some problems with the data.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Some emails were not sent.'));
        }

        // save form data
        $this->catalogSession->setSendfriendFormData($data);

        $url = $this->_url->getUrl('sendfriend/product/send', ['_current' => true]);
        $resultRedirect->setUrl($this->_redirect->error($url));
        return $resultRedirect;
    }
}
