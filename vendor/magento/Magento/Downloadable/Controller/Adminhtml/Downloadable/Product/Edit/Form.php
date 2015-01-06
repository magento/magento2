<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit;

class Form extends \Magento\Catalog\Controller\Adminhtml\Product\Edit
{
    /**
     * Load downloadable tab fieldsets
     *
     * @return void
     */
    public function execute()
    {
        $this->_initProduct();
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Magento\Downloadable\Block\Adminhtml\Catalog\Product\Edit\Tab\Downloadable',
                'admin.product.downloadable.information'
            )->toHtml()
        );
    }
}
