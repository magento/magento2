<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sendfriend\Controller\Product;

use Magento\Framework\Exception\NoSuchEntityException;

class Sendmail extends \Magento\Sendfriend\Controller\Product
{
    /** @var  \Magento\Catalog\Api\CategoryRepositoryInterface */
    protected $categoryRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Sendfriend\Model\Sendfriend $sendFriend
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Sendfriend\Model\Sendfriend $sendFriend,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($context, $coreRegistry, $formKeyValidator, $sendFriend, $productRepository);
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Send Email Post Action
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->_redirect('*/*/send', ['_current' => true]);
        }

        $product = $this->_initProduct();
        $model = $this->_initSendToFriendModel();
        $data = $this->getRequest()->getPost();

        if (!$product || !$data) {
            $this->_forward('noroute');
            return;
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

        $model->setSender($this->getRequest()->getPost('sender'));
        $model->setRecipients($this->getRequest()->getPost('recipients'));
        $model->setProduct($product);

        /* @var $session \Magento\Catalog\Model\Session */
        $catalogSession = $this->_objectManager->get('Magento\Catalog\Model\Session');
        try {
            $validate = $model->validate();
            if ($validate === true) {
                $model->send();
                $this->messageManager->addSuccess(__('The link to a friend was sent.'));
                $url = $product->getProductUrl();
                $this->getResponse()->setRedirect($this->_redirect->success($url));
                return;
            } else {
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                } else {
                    $this->messageManager->addError(__('We found some problems with the data.'));
                }
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Some emails were not sent.'));
        }

        // save form data
        $catalogSession->setSendfriendFormData($data);

        $url = $this->_objectManager->create('Magento\Framework\UrlInterface')->getUrl('*/*/send', ['_current' => true]);
        $this->getResponse()->setRedirect($this->_redirect->error($url));
    }
}
