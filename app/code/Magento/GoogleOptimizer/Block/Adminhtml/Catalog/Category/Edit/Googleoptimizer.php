<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit;

/**
 * Class \Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\Googleoptimizer
 *
 * @since 2.1.0
 */
class Googleoptimizer extends \Magento\Backend\Block\Template
{
    /**
     * @return string
     * @since 2.1.0
     */
    public function toHtml()
    {
        return $this->getLayout()->createBlock(
            \Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\GoogleoptimizerForm::class,
            'google-experiment-form'
        )->toHtml();
    }
}
