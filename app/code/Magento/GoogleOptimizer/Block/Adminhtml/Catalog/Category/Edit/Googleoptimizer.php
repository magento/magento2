<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit;

class Googleoptimizer extends \Magento\Backend\Block\Template
{
    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->getLayout()->createBlock(
            'Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\GoogleoptimizerForm',
            'google-experiment-form'
        )->toHtml();
    }
}
