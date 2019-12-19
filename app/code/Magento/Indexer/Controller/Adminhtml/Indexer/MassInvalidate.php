<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Controller\Adminhtml\Indexer;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Controller endpoint for mass action: invalidate index
 */
class MassInvalidate extends \Magento\Indexer\Controller\Adminhtml\Indexer implements HttpPostActionInterface
{
    /**
     * @var IndexerRegistry $indexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param Context $context
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        Context $context,
        IndexerRegistry $indexerRegistry
    ) {
        parent::__construct($context);
        $this->indexerRegistry = $indexerRegistry;
    }

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
                    /** @var \Magento\Framework\Indexer\IndexerInterface $model */
                    $model = $this->indexerRegistry->get($indexerId);
                    $model->invalidate();
                }
                $this->messageManager->addSuccess(
                    __('%1 indexer(s) were invalidated.', count($indexerIds))
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __("We couldn't invalidate indexer(s) because of an error.")
                );
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/list');
    }
}
