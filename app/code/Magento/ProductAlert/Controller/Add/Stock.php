<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Controller\Add;

use Magento\Framework\Exception\NoSuchEntityException;

class Stock extends \Magento\ProductAlert\Controller\Add
{
    /** @var  \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context, $customerSession);
        $this->productRepository = $productRepository;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $backUrl = $this->getRequest()->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED);
        $productId = (int)$this->getRequest()->getParam('product_id');
        if (!$backUrl || !$productId) {
            $this->_redirect('/');
            return;
        }

        try {
            $product = $this->productRepository->getById($productId);

            $model = $this->_objectManager->create(
                'Magento\ProductAlert\Model\Stock'
            )->setCustomerId(
                $this->_customerSession->getCustomerId()
            )->setProductId(
                $product->getId()
            )->setWebsiteId(
                $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getWebsiteId()
            );
            $model->save();
            $this->messageManager->addSuccess(__('Alert subscription has been saved.'));
        } catch (NoSuchEntityException $noEntityException) {
            /* @var $product \Magento\Catalog\Model\Product */
            $this->messageManager->addError(__('There are not enough parameters.'));
            $this->getResponse()->setRedirect($backUrl);
            return;
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
