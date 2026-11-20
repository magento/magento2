<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\ProductAlert\Controller\Add;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\ProductAlert\Controller\Add as AddController;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Controller for notifying about stock.
 */
class Stock extends AddController implements HttpGetActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($context, $customerSession);
        $this->storeManager = $storeManager ?: $this->_objectManager
            ->get(StoreManagerInterface::class);
    }

    /**
     * Check if URL is internal
     *
     * @param string $url
     * @return bool
     * @throws NoSuchEntityException
     */
    private function isInternal($url): bool
    {
        if ($url === null || strpos($url, 'http') === false) {
            return false;
        }
        $currentStore = $this->storeManager->getStore();
        return strpos($url, (string) $currentStore->getBaseUrl()) === 0
            || strpos($url, (string) $currentStore->getBaseUrl(UrlInterface::URL_TYPE_LINK, true)) === 0;
    }

    /**
     * Method for adding info about product alert stock.
     *
     * @return Redirect
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

        try {
            /* @var $product Product */
            $product = $this->productRepository->getById($productId);
            $store = $this->storeManager->getStore();
            /** @var \Magento\ProductAlert\Model\Stock $model */
            $model = $this->_objectManager->create(\Magento\ProductAlert\Model\Stock::class)
                ->setCustomerId($this->customerSession->getCustomerId())
                ->setProductId($product->getId())
                ->setWebsiteId($store->getWebsiteId())
                ->setStoreId($store->getId());
            $model->save();
            $this->messageManager->addSuccessMessage(__('Alert subscription has been saved.'));
        } catch (NoSuchEntityException $noEntityException) {
            $this->messageManager->addErrorMessage(__('There are not enough parameters.'));
            $this->isInternal($backUrl)
                ? $resultRedirect->setUrl($backUrl)
                : $resultRedirect->setPath('/');
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
