<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;

class ExportCsv extends \Magento\Backend\App\Action
{
    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->_fileFactory = $fileFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::shipment');
    }

    /**
     * Export shipment grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $fileName = 'shipments.csv';
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultLayoutFactory->create();
        $grid = $resultLayout->getLayout()->getChildBlock('sales.shipment.grid', 'grid.export');
        return $this->_fileFactory->create(
            $fileName,
            $grid->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
