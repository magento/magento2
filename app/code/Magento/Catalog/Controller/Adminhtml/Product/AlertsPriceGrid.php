<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\AlertsPriceGrid
 *
 * @since 2.0.0
 */
class AlertsPriceGrid extends AbstractProductGrid
{
    /**
     * Get alerts price grid
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
     */
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
