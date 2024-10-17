<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductAlert\Controller\Unsubscribe;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\ProductAlert\Controller\Unsubscribe as UnsubscribeController;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Price
 */
class Price extends UnsubscribeController implements HttpGetActionInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface|null
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager = null
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);

        parent::__construct($context, $customerSession);
    }

    /**
     * Unsubscribe action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$productId) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        try {
            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->productRepository->getById($productId);
            if (!$product->isVisibleInCatalog()) {
                $this->messageManager->addErrorMessage(
                    __("The product wasn't found. Verify the product and try again.")
                );
                $resultRedirect->setPath('customer/account/');
                return $resultRedirect;
            }

            /** @var \Magento\ProductAlert\Model\Price $model */
            $model = $this->_objectManager->create(\Magento\ProductAlert\Model\Price::class)
                ->setCustomerId($this->customerSession->getCustomerId())
                ->setProductId($product->getId())
                ->setWebsiteId($this->storeManager->getStore()->getWebsiteId())
                ->setStoreId($this->storeManager->getStore()->getId())
                ->loadByParam();

            if ($model->getId()) {
                $model->delete();
            }

            $this->messageManager->addSuccessMessage(__('You deleted the alert subscription.'));
        } catch (NoSuchEntityException $noEntityException) {
            $this->messageManager->addErrorMessage(__("The product wasn't found. Verify the product and try again."));
            $resultRedirect->setPath('customer/account/');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __("The alert subscription couldn't update at this time. Please try again later.")
            );
        }
        $resultRedirect->setUrl($product->getProductUrl());
        return $resultRedirect;
    }
}
