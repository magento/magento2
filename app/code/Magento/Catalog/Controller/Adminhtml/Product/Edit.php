<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Edit extends \Magento\Catalog\Controller\Adminhtml\Product implements HttpGetActionInterface
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['edit'];

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Product edit form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $store = $storeManager->getStore($storeId);
        $storeManager->setCurrentStore($store->getCode());
        $productId = (int) $this->getRequest()->getParam('id');
        $product = $this->productBuilder->build($this->getRequest());

        if (($productId && !$product->getEntityId())) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('This product doesn\'t exist.'));
            return $resultRedirect->setPath('catalog/*/');
        } elseif ($productId === 0) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('Invalid product id. Should be numeric value greater than 0'));
            return $resultRedirect->setPath('catalog/*/');
        }

        $this->_eventManager->dispatch('catalog_product_edit_action', ['product' => $product]);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('catalog_product_' . $product->getTypeId());
        $resultPage->setActiveMenu('Magento_Catalog::catalog_products');
        $resultPage->getConfig()->getTitle()->prepend(__('Products'));
        $resultPage->getConfig()->getTitle()->prepend($product->getName());

        if (!$this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->isSingleStoreMode()
            &&
            ($switchBlock = $resultPage->getLayout()->getBlock('store_switcher'))
        ) {
            $switchBlock->setDefaultStoreName(__('Default Values'))
                ->setWebsiteIds($product->getWebsiteIds())
                ->setSwitchUrl(
                    $this->getUrl(
                        'catalog/*/*',
                        ['_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null]
                    )
                );
        }

        return $resultPage;
    }
}
