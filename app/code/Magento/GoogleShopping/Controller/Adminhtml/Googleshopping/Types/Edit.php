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
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_initItemType();
        $typeId = $this->_coreRegistry->registry('current_item_type')->getTypeId();

        try {
            $result = [];
            if ($typeId) {
                $collection = $this->_objectManager
                    ->create('Magento\GoogleShopping\Model\Resource\Attribute\Collection')
                    ->addTypeFilter($typeId)->load();
                foreach ($collection as $attribute) {
                    $result[] = $attribute->getData();
                }
            }

            $this->_coreRegistry->register('attributes', $result);

            $breadcrumbLabel = $typeId ? __('Edit attribute set mapping') : __('New attribute set mapping');

            $resultPage = $this->initPage();
            $resultPage->addBreadcrumb($breadcrumbLabel, $breadcrumbLabel)
                ->addContent(
                    $resultPage->getLayout()->createBlock('Magento\GoogleShopping\Block\Adminhtml\Types\Edit')
                );

            $resultPage->getConfig()->getTitle()->prepend(__('Google Content Attribute Mapping'));
            $resultPage->getConfig()->getTitle()->prepend(__('Google Content Attributes'));
            return $resultPage;
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError(__('We can\'t edit Attribute Set Mapping right now.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('adminhtml/*/index');
        }
    }
}
