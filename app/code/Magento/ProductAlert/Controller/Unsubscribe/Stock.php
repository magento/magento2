<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductAlert\Controller\Unsubscribe;

use Magento\ProductAlert\Controller\Unsubscribe as UnsubscribeController;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ProductAlert\Model\StockFactory;

class Stock extends UnsubscribeController
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\ProductAlert\Model\StockFactory
     */
    private $stockFactory;

    /**
     * Stock constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface|null $storeManager
     * @param StockFactory|null $stockFactory
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager = null,
        StockFactory $stockFactory = null
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->stockFactory = $stockFactory ?: ObjectManager::getInstance()->get(StockFactory::class);
        parent::__construct($context, $customerSession);
    }

    /**
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
            $product = $this->productRepository->getById($productId);
            if (!$product->isVisibleInCatalog()) {
                throw new NoSuchEntityException();
            }

            $model = $this->stockFactory->create()
                ->setCustomerId($this->customerSession->getCustomerId())
                ->setProductId($product->getId())
                ->setWebsiteId(
                    $this->storeManager
                        ->getStore()
                        ->getWebsiteId()
                )->setStoreId(
                    $this->storeManager
                        ->getStore()
                        ->getStoreId()
                )
                ->loadByParam();
            if ($model->getId()) {
                $model->delete();
            }
            $this->messageManager->addSuccess(__('You will no longer receive stock alerts for this product.'));
        } catch (NoSuchEntityException $noEntityException) {
            $this->messageManager->addError(__('The product was not found.'));
            $resultRedirect->setPath('customer/account/');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __("The alert subscription couldn't update at this time. Please try again later.")
            );
        }
        $resultRedirect->setUrl($product->getProductUrl());
        return $resultRedirect;
    }
}
