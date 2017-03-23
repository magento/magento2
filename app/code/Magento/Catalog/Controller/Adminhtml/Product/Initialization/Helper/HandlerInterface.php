<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

interface HandlerInterface
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function handle(\Magento\Catalog\Model\Product $product);
}
