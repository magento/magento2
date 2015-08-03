<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

class Pdfcreditmemos extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var string
     */
    protected $collection = 'Magento\Sales\Model\Resource\Order\Creditmemo\Grid\Collection';

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }

    /**
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     * @throws \Exception
     */
    public function massAction(AbstractCollection $collection)
    {
        if (!isset($pdf)) {
            $pdf = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Creditmemo')->getPdf($collection);
        } else {
            $pages = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Creditmemo')->getPdf($collection);
            $pdf->pages = array_merge($pdf->pages, $pages->pages);
        }
        $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');

        return $this->_fileFactory->create(
            'creditmemo' . $date . '.pdf',
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
