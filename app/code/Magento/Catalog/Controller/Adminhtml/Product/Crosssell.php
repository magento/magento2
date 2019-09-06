<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class Crosssell
 *
 * @package Magento\Catalog\Controller\Adminhtml\Product
 * @deprecated Not used since cross-sell products grid moved to UI components.
 * @see Magento_Catalog::view/adminhtml/ui_component/crosssell_product_listing.xml
 */
class Crosssell extends Product implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Get crosssell products grid and serializer block
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->productBuilder->build($this->getRequest());
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('catalog.product.edit.tab.crosssell')
            ->setProductsCrossSell($this->getRequest()->getPost('products_crosssell', null));
        return $resultLayout;
    }
}
