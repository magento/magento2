<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Controller\Add;

use Magento\ProductAlert\Controller\Add as AddController;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class \Magento\ProductAlert\Controller\Add\Price
 *
 * @since 2.0.0
 */
class Price extends AddController
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var  \Magento\Catalog\Api\ProductRepositoryInterface
     * @since 2.0.0
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function isInternal($url)
    {
        if (strpos($url, 'http') === false) {
            return false;
        }
        $currentStore = $this->storeManager->getStore();
        return strpos($url, $currentStore->getBaseUrl()) === 0
            || strpos($url, $currentStore->getBaseUrl(UrlInterface::URL_TYPE_LINK, true)) === 0;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        $backUrl = $this->getRequest()->getParam(Action::PARAM_NAME_URL_ENCODED);
        $productId = (int)$this->getRequest()->getParam('product_id');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$backUrl || !$productId) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        try {
            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->productRepository->getById($productId);
            /** @var \Magento\ProductAlert\Model\Price $model */
            $model = $this->_objectManager->create(\Magento\ProductAlert\Model\Price::class)
                ->setCustomerId($this->customerSession->getCustomerId())
                ->setProductId($product->getId())
                ->setPrice($product->getFinalPrice())
                ->setWebsiteId(
                    $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)
                        ->getStore()
                        ->getWebsiteId()
                );
            $model->save();
            $this->messageManager->addSuccess(__('You saved the alert subscription.'));
        } catch (NoSuchEntityException $noEntityException) {
            $this->messageManager->addError(__('There are not enough parameters.'));
            if ($this->isInternal($backUrl)) {
                $resultRedirect->setUrl($backUrl);
            } else {
                $resultRedirect->setPath('/');
            }
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t update the alert subscription right now.'));
        }
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
}
