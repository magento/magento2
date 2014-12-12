<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Indexer\Controller\Adminhtml\Indexer;

class MassOnTheFly extends \Magento\Indexer\Controller\Adminhtml\Indexer
{
    /**
     * Turn mview off for the given indexers
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
                    $model->setScheduled(false);
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 indexer(s) have been turned Update on Save mode on.', count($indexerIds))
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
