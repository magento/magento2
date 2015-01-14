<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class Edit extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * Edit attribute set mapping
     *
     * @return void
     */
    public function execute()
    {
        $this->_initItemType();
        $typeId = $this->_coreRegistry->registry('current_item_type')->getTypeId();

        try {
            $result = [];
            if ($typeId) {
                $collection = $this->_objectManager->create(
                    'Magento\GoogleShopping\Model\Resource\Attribute\Collection'
                )->addTypeFilter(
                    $typeId
                )->load();
                foreach ($collection as $attribute) {
                    $result[] = $attribute->getData();
                }
            }

            $this->_coreRegistry->register('attributes', $result);

            $breadcrumbLabel = $typeId ? __('Edit attribute set mapping') : __('New attribute set mapping');
            $this->_initAction()->_addBreadcrumb(
                $breadcrumbLabel,
                $breadcrumbLabel
            )->_addContent(
                $this->_view->getLayout()->createBlock('Magento\GoogleShopping\Block\Adminhtml\Types\Edit')
            );
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Google Content Attribute Mapping'));
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Google Content Attributes'));
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError(__("We can't edit Attribute Set Mapping."));
            $this->_redirect('adminhtml/*/index');
        }
    }
}
