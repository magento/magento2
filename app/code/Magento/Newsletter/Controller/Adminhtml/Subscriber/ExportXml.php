<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Subscriber;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \Magento\Newsletter\Controller\Adminhtml\Subscriber
{
    /**
     * Export subscribers grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'subscribers.xml';
        $content = $this->_view->getLayout()->getChildBlock('adminhtml.newslettrer.subscriber.grid', 'grid.export');
        return $this->_fileFactory->create(
            $fileName,
            $content->getExcelFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
