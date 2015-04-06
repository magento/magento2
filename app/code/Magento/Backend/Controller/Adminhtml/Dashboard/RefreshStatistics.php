<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Controller\Adminhtml\Dashboard;

class RefreshStatistics extends \Magento\Reports\Controller\Adminhtml\Report\Statistics
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param array $reportTypes
     * @param \Psr\Log\LoggerInterface $logger
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
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collectionsNames = array_values($this->reportTypes);
        foreach ($collectionsNames as $collectionName) {
            $this->_objectManager->create($collectionName)->aggregate();
        }
        $this->messageManager->addSuccess(__('We updated lifetime statistic.'));

        return $this->getDefaultResult();
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultResult()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*');
    }
}
