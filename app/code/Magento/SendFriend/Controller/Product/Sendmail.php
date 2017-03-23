<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\SendFriend\Controller\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;

class Sendmail extends \Magento\SendFriend\Controller\Product
{
    /** @var  \Magento\Catalog\Api\CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\SendFriend\Model\SendFriend $sendFriend
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\Session $catalogSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\SendFriend\Model\SendFriend $sendFriend,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        parent::__construct($context, $coreRegistry, $formKeyValidator, $sendFriend, $productRepository);
        $this->categoryRepository = $categoryRepository;
        $this->catalogSession = $catalogSession;
    }

    /**
     * Send Email Post Action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setPath('sendfriend/product/send', ['_current' => true]);
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
}
