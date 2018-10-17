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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        if (!$this->initProduct()) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            return $resultForward->forward('noroute');
        }

        return $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
    }
}
