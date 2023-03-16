<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Layout as ResultLayout;
use Magento\Search\Controller\Adminhtml\Term as TermController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportSearchExcel extends TermController
{
    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        protected readonly FileFactory $fileFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Export search report to Excel XML format
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function execute()
    {
        /** @var ResultLayout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $content = $resultLayout->getLayout()->getChildBlock('adminhtml.report.search.grid', 'grid.export');
        return $this->fileFactory->create('search.xml', $content->getExcelFile(), DirectoryList::VAR_DIR);
    }
}
