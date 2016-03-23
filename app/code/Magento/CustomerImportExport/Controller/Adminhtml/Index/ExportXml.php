<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Controller\Adminhtml\Index;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Index
 */
class ExportXml extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Export customer grid to XML format
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'customers.xml';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock  */
        $exportBlock = $this->_view->getLayout()->getChildBlock('admin.block.customer.grid', 'grid.export');
        $content = $exportBlock->getExcelFile($fileName);
        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
