<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Review\Controller\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

class ListAjax extends ProductController
{
    /**
     * Show list of product's reviews
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        if (!$this->initProduct()) {
            throw new LocalizedException(__('Cannot initialize product'));
        } else {
            /** @var \Magento\Framework\View\Result\Layout $resultLayout */
            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        }

        return $resultLayout;
    }
}
