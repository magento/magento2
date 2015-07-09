<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

abstract class Pdfinvoices extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Magento\Sales\Model\Resource\Order\Invoice\Grid\Collection';

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
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
    }

    /**
     * Save collection items to pdf invoices
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface
     * @throws \Exception
     */
    public function massAction(AbstractCollection $collection)
    {
        if (!isset($pdf)) {
            $pdf = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Invoice')->getPdf($collection);
        } else {
            $pages = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Invoice')->getPdf($collection);
            $pdf->pages = array_merge($pdf->pages, $pages->pages);
        }
        $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');

        return $this->_fileFactory->create(
            'invoice' . $date . '.pdf',
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
