<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Controller\Unsubscribe;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ProductAlert\Controller\Unsubscribe as UnsubscribeController;
use Magento\ProductAlert\Model\PriceFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Price
 */
class Price extends UnsubscribeController implements HttpGetActionInterface
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
     * @var PriceFactory
     */
    private $priceFactory;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface|null $storeManager
     * @param PriceFactory|null $priceFactory
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ProductRepositoryInterface $productRepository,
        ?StoreManagerInterface $storeManager = null,
        ?PriceFactory $priceFactory = null
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->priceFactory = $priceFactory ?? ObjectManager::getInstance()->get(PriceFactory::class);
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

            $store = $this->storeManager->getStore();
            $model = $this->priceFactory->create()
                ->setCustomerId($this->customerSession->getCustomerId())
                ->setProductId($product->getId())
                ->setWebsiteId($store->getWebsiteId())
                ->setStoreId($store->getId())
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
        $resultRedirect->setPath('productalert/customer/index');
        return $resultRedirect;
    }
}
