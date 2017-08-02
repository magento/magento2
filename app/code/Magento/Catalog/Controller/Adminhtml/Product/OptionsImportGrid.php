<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\OptionsImportGrid
 *
 * @since 2.0.0
 */
class OptionsImportGrid extends AbstractProductGrid
{
    /**
     * Show product grid for custom options import popup
     *
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
