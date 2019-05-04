<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Controller\Product;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Review\Controller\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class ListAjax extends ProductController implements HttpGetActionInterface
{
    /**
     * Show list of product's reviews
     *
     * @return ResponseInterface|ResultInterface|Layout
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
