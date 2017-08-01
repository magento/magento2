<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Controller\Adminhtml\Dashboard;

/**
 * Class \Magento\Backend\Controller\Adminhtml\Dashboard\RefreshStatistics
 *
 * @since 2.0.0
 */
class RefreshStatistics extends \Magento\Reports\Controller\Adminhtml\Report\Statistics
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param array $reportTypes
     * @param \Psr\Log\LoggerInterface $logger
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        array $reportTypes,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $dateFilter, $reportTypes);
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
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
            $this->logger->critical($e);
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*');
    }
}
