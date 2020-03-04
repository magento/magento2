<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductAlert\Controller\Add;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\ProductAlert\Controller\Add as AddController;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Controller for notifying about price.
 */
class Price extends AddController implements HttpGetActionInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Price constructor.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        parent::__construct($context, $customerSession);
    }

    /**
     * Check if URL is internal
     *
     * @param string $url
     * @return bool
     * @throws NoSuchEntityException
     */
    protected function isInternal($url)
    {
        if (strpos($url, 'http') === false) {
            return false;
        }
        $currentStore = $this->storeManager->getStore();
        return strpos($url, (string) $currentStore->getBaseUrl()) === 0
            || strpos($url, (string) $currentStore->getBaseUrl(UrlInterface::URL_TYPE_LINK, true)) === 0;
    }

    /**
     * Method for adding info about product alert price.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $backUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);
        $productId = (int)$this->getRequest()->getParam('product_id');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$backUrl || !$productId) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        $store = $this->storeManager->getStore();
        try {
            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->productRepository->getById($productId);
            /** @var \Magento\ProductAlert\Model\Price $model */
            $model = $this->_objectManager->create(\Magento\ProductAlert\Model\Price::class)
                ->setCustomerId($this->customerSession->getCustomerId())
                ->setProductId($product->getId())
                ->setPrice($product->getFinalPrice())
                ->setWebsiteId($store->getWebsiteId())
                ->setStoreId($store->getId());
            $model->save();
            $this->messageManager->addSuccessMessage(__('You saved the alert subscription.'));
        } catch (NoSuchEntityException $noEntityException) {
            $this->messageManager->addErrorMessage(__('There are not enough parameters.'));
            if ($this->isInternal($backUrl)) {
                $resultRedirect->setUrl($backUrl);
            } else {
                $resultRedirect->setPath('/');
            }
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __("The alert subscription couldn't update at this time. Please try again later.")
            );
        }
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
}
