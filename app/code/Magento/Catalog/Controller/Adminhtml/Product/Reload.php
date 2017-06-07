<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Backend reload of product create/edit form
 */
class Reload extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        StoreManagerInterface $storeManager = null
    ) {
        parent::__construct($context, $productBuilder);

        $this->storeManager = $storeManager ?: $this->_objectManager->get(
            StoreManagerInterface::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('set')) {
            return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
        }

        $product = $this->productBuilder->build($this->getRequest());

        $store = $this->storeManager->getStore($product->getStoreId());
        $this->storeManager->setCurrentStore($store->getCode());

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $resultLayout->getLayout()->getUpdate()->addHandle(['catalog_product_' . $product->getTypeId()]);
        $resultLayout->getLayout()->getUpdate()->removeHandle('default');
        $resultLayout->setHeader('Content-Type', 'application/json', true);

        return $resultLayout;
    }
}
