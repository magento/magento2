<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Selection;

class Search extends \Magento\Backend\App\Action
{
    /**
     * Search result grid with available products for Google Content
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Magento\GoogleShopping\Block\Adminhtml\Items\Product'
            )->setIndex(
                $this->getRequest()->getParam('index')
            )->setFirstShow(
                true
            )->toHtml()
        );
    }
}
