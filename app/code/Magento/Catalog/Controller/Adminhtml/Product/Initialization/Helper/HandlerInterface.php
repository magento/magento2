<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

/**
 * Interface \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface
 *
 * @since 2.0.0
 */
interface HandlerInterface
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @since 2.0.0
     */
    public function handle(\Magento\Catalog\Model\Product $product);
}
