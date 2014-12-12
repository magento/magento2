<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ImportExport\Controller\Adminhtml\Export;

class GetFilter extends \Magento\ImportExport\Controller\Adminhtml\Export
{
    /**
     * Get grid-filter of entity attributes action.
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest() && $data) {
            try {
                $this->_view->loadLayout();

                /** @var $attrFilterBlock \Magento\ImportExport\Block\Adminhtml\Export\Filter */
                $attrFilterBlock = $this->_view->getLayout()->getBlock('export.filter');
                /** @var $export \Magento\ImportExport\Model\Export */
                $export = $this->_objectManager->create('Magento\ImportExport\Model\Export');
                $export->setData($data);

                $export->filterAttributeCollection(
                    $attrFilterBlock->prepareCollection($export->getEntityAttributeCollection())
                );
                $this->_view->renderLayout();
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Please correct the data sent.'));
        }
        $this->_redirect('adminhtml/*/index');
    }
}
