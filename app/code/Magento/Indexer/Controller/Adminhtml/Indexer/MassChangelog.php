<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Controller\Adminhtml\Indexer;

class MassChangelog extends \Magento\Indexer\Controller\Adminhtml\Indexer
{
    /**
     * Turn mview on for the given indexers
     *
     * @return void
     */
    public function execute()
    {
        $indexerIds = $this->getRequest()->getParam('indexer_ids');
        if (!is_array($indexerIds)) {
            $this->messageManager->addError(__('Please select indexers.'));
        } else {
            try {
                foreach ($indexerIds as $indexerId) {
                    /** @var \Magento\Indexer\Model\IndexerInterface $model */
                    $model = $this->_objectManager->get('Magento\Indexer\Model\IndexerRegistry')->get($indexerId);
                    $model->setScheduled(true);
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 indexer(s) have been turned Update by Schedule mode on.', count($indexerIds))
                );
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __("We couldn't change indexer(s)' mode because of an error.")
                );
            }
        }
        $this->_redirect('*/*/list');
    }
}
