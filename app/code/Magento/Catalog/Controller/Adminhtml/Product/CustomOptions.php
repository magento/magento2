<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\CustomOptions
 *
 * @since 2.0.0
 */
class CustomOptions extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     * @since 2.0.0
     */
    protected $resultLayoutFactory;

    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->registry = $registry;
        parent::__construct($context, $productBuilder);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Show custom options in JSON format for specified products
     *
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function execute()
    {
        $this->registry->register('import_option_products', $this->getRequest()->getPost('products'));
        return $this->resultLayoutFactory->create();
    }
}
