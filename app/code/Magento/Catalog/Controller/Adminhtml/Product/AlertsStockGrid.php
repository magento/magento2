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

class AlertsStockGrid extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Constructor alert stock grid
     *
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
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
     */
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
