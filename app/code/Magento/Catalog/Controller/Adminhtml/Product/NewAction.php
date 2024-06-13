<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\RegexValidator;

class NewAction extends \Magento\Catalog\Controller\Adminhtml\Product implements HttpGetActionInterface
{
    /**
     * @var Initialization\StockDataFilter
     * @deprecated 101.0.0
     * @see Initialization\StockDataFilter
     */
    protected $stockFilter;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var RegexValidator
     */
    private RegexValidator $regexValidator;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Initialization\StockDataFilter $stockFilter
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param RegexValidator|null $regexValidator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        Initialization\StockDataFilter $stockFilter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        ?RegexValidator $regexValidator = null
    ) {
        $this->stockFilter = $stockFilter;
        parent::__construct($context, $productBuilder);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->regexValidator = $regexValidator
            ?: ObjectManager::getInstance()->get(RegexValidator::class);
    }

    /**
     * Create new product page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $typeId = $this->getRequest()->getParam('type');
        if (!$this->regexValidator->validateParamRegex($typeId)) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        if (!$this->getRequest()->getParam('set')) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $product = $this->productBuilder->build($this->getRequest());
        $this->_eventManager->dispatch('catalog_product_new_action', ['product' => $product]);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        if ($this->getRequest()->getParam('popup')) {
            $resultPage->addHandle(['popup', 'catalog_product_' . $product->getTypeId()]);
        } else {
            $resultPage->addHandle(['catalog_product_' . $product->getTypeId()]);
            $resultPage->setActiveMenu('Magento_Catalog::catalog_products');
            $resultPage->getConfig()->getTitle()->prepend(__('Products'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Product'));
        }

        return $resultPage;
    }
}
