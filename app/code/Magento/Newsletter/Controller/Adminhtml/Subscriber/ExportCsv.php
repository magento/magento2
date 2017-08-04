<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Subscriber;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Subscriber\ExportCsv
 *
 */
class ExportCsv extends \Magento\Newsletter\Controller\Adminhtml\Subscriber
{
    /**
     * Export subscribers grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'subscribers.csv';
        $content = $this->_view->getLayout()->getChildBlock('adminhtml.newslettrer.subscriber.grid', 'grid.export');

        return $this->_fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
