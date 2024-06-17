<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Controller\Adminhtml\Indexer;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller endpoint for mass action: set index mode as 'Update by Schedule'
 */
class MassChangelog extends \Magento\Indexer\Controller\Adminhtml\Indexer implements HttpPostActionInterface
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
            $this->messageManager->addErrorMessage(__('Please select indexers.'));
        } else {
            $updatedIndexersCount = 0;

            try {
                foreach ($indexerIds as $indexerId) {
                    /** @var \Magento\Framework\Indexer\IndexerInterface $model */
                    $model = $this->_objectManager->get(
                        \Magento\Framework\Indexer\IndexerRegistry::class
                    )->get($indexerId);

                    if (!$model->isScheduled()) {
                        $model->setScheduled(true);
                        $updatedIndexersCount++;
                    }
                }

                $this->messageManager->addSuccessMessage(
                    __(
                        '%1 indexer(s) have been updated to "Update by Schedule" mode. 
                        %2 skipped because there was nothing to change.',
                        $updatedIndexersCount,
                        count($indexerIds) - $updatedIndexersCount
                    )
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
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
