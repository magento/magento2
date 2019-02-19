<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Api\Data\ProductInterface;

/** Returns information about selected product by product id. Returns empty array if product don't exist */
class GetSelected extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * Search constructor.
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() : \Magento\Framework\Controller\ResultInterface
    {
        $productId = $this->getRequest()->getParam('productId');
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect(ProductInterface::NAME);
        $productCollection->addIdFilter($productId);
        $option = [];
        /** @var ProductInterface $product */
        if (!empty($productCollection->getFirstItem()->getData())) {
            $product = $productCollection->getFirstItem();
            $option = [
                'value' => $productId,
                'label' => $product->getName(),
                'is_active' => $product->getStatus(),
                'path' => $product->getSku(),
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($option);
    }
}
