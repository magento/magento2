<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Associated;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * Class \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Associated\Grid
 *
 * @since 2.0.0
 */
class Grid extends Action
{
    /**
     * @var LayoutFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param LayoutFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        LayoutFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
