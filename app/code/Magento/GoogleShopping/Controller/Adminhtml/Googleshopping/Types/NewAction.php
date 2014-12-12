<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class NewAction extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * Create new attribute set mapping
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_initItemType();
            $this->_initAction()->_addBreadcrumb(
                __('New attribute set mapping'),
                __('New attribute set mapping')
            )->_addContent(
                $this->_view->getLayout()->createBlock('Magento\GoogleShopping\Block\Adminhtml\Types\Edit')
            );
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Google Content Attributes'));
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Google Content Attribute Mapping'));
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->messageManager->addError(__("We can't create Attribute Set Mapping."));
            $this->_redirect('adminhtml/*/index', ['store' => $this->_getStore()->getId()]);
        }
    }
}
