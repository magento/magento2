<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;

class ExportSearchCsv extends \Magento\Search\Controller\Adminhtml\Term
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_httpFileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, FileFactory $fileFactory)
    {
        $this->_httpFileFactory = $fileFactory;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Export search report grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $content = $this->_view->getLayout()->getChildBlock('adminhtml.report.search.grid', 'grid.export');
        return $this->_httpFileFactory->create('search.csv', $content->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
