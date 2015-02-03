<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Controller\Adminhtml\Dashboard;

class RefreshStatistics extends \Magento\Reports\Controller\Adminhtml\Report\Statistics
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collectionsNames = array_values($this->reportTypes);
            foreach ($collectionsNames as $collectionName) {
                $this->_objectManager->create($collectionName)->aggregate();
            }
            $this->messageManager->addSuccess(__('We updated lifetime statistic.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t refresh lifetime statistics.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        return $this->resultRedirectFactory->create()->setPath('*/*');
    }
}
