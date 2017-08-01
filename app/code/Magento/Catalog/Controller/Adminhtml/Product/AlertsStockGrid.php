<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\Result;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\AlertsStockGrid
 *
 * @since 2.0.0
 */
class AlertsStockGrid extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var LayoutFactory
     * @since 2.0.0
     */
    protected $resultLayoutFactory;

    /**
     * Constructor alert stock grid
     *
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        LayoutFactory $resultLayoutFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context, $productBuilder);
    }

    /**
     * Get alerts stock grid
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
