<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

class JsonProductInfo extends \Magento\Review\Controller\Adminhtml\Product
{
    /** @var  \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context, $coreRegistry, $reviewFactory, $ratingFactory);
        $this->productRepository = $productRepository;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $response = new \Magento\Framework\Object();
        $id = $this->getRequest()->getParam('id');
        if (intval($id) > 0) {
            $product = $this->productRepository->getById($id);

            $response->setId($id);
            $response->addData($product->getData());
            $response->setError(0);
        } else {
            $response->setError(1);
            $response->setMessage(__('We can\'t get the product ID.'));
        }
        $this->getResponse()->representJson($response->toJSON());
    }
}
