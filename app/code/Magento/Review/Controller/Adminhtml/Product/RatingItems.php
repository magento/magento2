<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Review\Controller\Adminhtml\Product;

class RatingItems extends \Magento\Review\Controller\Adminhtml\Product
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Magento\Review\Block\Adminhtml\Rating\Detailed'
            )->setIndependentMode()->toHtml()
        );
    }
}
