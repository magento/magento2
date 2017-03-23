<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            \Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\GoogleoptimizerForm::class,
            'google-experiment-form'
        )->toHtml();
    }
}
