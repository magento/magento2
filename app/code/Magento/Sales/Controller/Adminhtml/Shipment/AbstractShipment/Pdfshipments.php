<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

abstract class Pdfshipments extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var FileFactory
     */
    protected $_fileFactory;
    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = 'Magento\Sales\Model\Resource\Order\Shipment\Grid\Collection';

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(Context $context, FileFactory $fileFactory)
    {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::shipment');
    }

    /**
     * @param AbstractCollection $collection
     * @return $this|ResponseInterface
     * @throws \Exception
     */
    public function massAction(AbstractCollection $collection)
    {
        if (!isset($pdf)) {
            $pdf = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Shipment')->getPdf($collection);
        } else {
            $pages = $this->_objectManager->create('Magento\Sales\Model\Order\Pdf\Shipment')->getPdf($collection);
            $pdf->pages = array_merge($pdf->pages, $pages->pages);
        }
        $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
        return $this->_fileFactory->create(
            'packingslip' . $date . '.pdf',
            $pdf->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
