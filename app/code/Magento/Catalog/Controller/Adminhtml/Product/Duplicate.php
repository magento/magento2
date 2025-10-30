<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\App\ObjectManager;

class Duplicate extends \Magento\Catalog\Controller\Adminhtml\Product implements
    \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        ?\Psr\Log\LoggerInterface $logger = null
    ) {
        $this->productCopier = $productCopier;
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);
        parent::__construct($context, $productBuilder);
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
            $this->messageManager->addSuccessMessage(__('You duplicated the product.'));
            $resultRedirect->setPath('catalog/*/edit', ['_current' => true, 'id' => $newProduct->getId()]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('catalog/*/edit', ['_current' => true]);
        }
        return $resultRedirect;
    }
}
