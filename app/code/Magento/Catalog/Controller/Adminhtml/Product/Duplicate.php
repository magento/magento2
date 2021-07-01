<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Catalog\Model\Product\Copier;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * Class Duplicate product
 */
class Duplicate extends Product implements HttpGetActionInterface
{
    /**
     * @var Copier
     */
    protected $productCopier;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Copier $productCopier
     * @param LoggerInterface|null $logger
     * @param ProductFactory|null $productFactory
     */
    public function __construct(
        Context $context,
        Product\Builder $productBuilder,
        Copier $productCopier,
        LoggerInterface $logger = null,
        ?ProductFactory $productFactory = null
    ) {
        $this->productCopier = $productCopier;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->productFactory = $productFactory ?: ObjectManager::getInstance()->get(ProductFactory::class);
        parent::__construct($context, $productBuilder);
    }

    /**
     * Create product duplicate
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $product = $this->productBuilder->build($this->getRequest());
        try {
            $newProduct = $this->productCopier->copy($product, $this->productFactory->create());
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
