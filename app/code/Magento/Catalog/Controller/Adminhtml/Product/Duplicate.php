<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;

class Duplicate extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->productCopier = $productCopier;
        parent::__construct($context, $productBuilder);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Create product duplicate
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $product = $this->productBuilder->build($this->getRequest());
        try {
            $newProduct = $this->productCopier->copy($product);
            $this->messageManager->addSuccess(__('You duplicated the product.'));
            $resultRedirect->setPath('catalog/*/edit', ['_current' => true, 'id' => $newProduct->getId()]);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError($e->getMessage());
            $resultRedirect->setPath('catalog/*/edit', ['_current' => true]);
        }
        return $resultRedirect;
    }
}
