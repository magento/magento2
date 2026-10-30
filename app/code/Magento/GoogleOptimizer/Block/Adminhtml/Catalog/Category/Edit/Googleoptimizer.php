<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
            \Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\GoogleoptimizerForm::class,
            'google-experiment-form'
        )->toHtml();
    }
}
