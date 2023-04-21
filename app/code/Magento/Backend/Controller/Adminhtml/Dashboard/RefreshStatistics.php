<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Reports\Controller\Adminhtml\Report\Statistics;
use Psr\Log\LoggerInterface;

/**
 * Refresh Dashboard statistics action.
 */
class RefreshStatistics extends Statistics implements HttpPostActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param Date $dateFilter
     * @param array $reportTypes
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Date $dateFilter,
        array $reportTypes,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $dateFilter, $reportTypes);
        $this->logger = $logger;
    }

    /**
     * Refresh statistics.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collectionsNames = array_values($this->reportTypes);
            foreach ($collectionsNames as $collectionName) {
                $this->_objectManager->create($collectionName)->aggregate();
            }
            $this->messageManager->addSuccessMessage(__('We updated lifetime statistic.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t refresh lifetime statistics.'));
            $this->logger->critical($e);
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*');
    }
}
