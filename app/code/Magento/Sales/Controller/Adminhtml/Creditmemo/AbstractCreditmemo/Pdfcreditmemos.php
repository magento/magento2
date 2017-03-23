<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory;

/**
 * Class Pdfcreditmemos
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Pdfcreditmemos extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Creditmemo
     */
    protected $pdfCreditmemo;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param Creditmemo $pdfCreditmemo
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Creditmemo $pdfCreditmemo,
        DateTime $dateTime,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->pdfCreditmemo = $pdfCreditmemo;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $filter);
    }

    /**
     * @param AbstractCollection $collection
     * @return ResponseInterface
     * @throws \Exception
     * @throws \Zend_Pdf_Exception
     */
    public function massAction(AbstractCollection $collection)
    {
        return $this->fileFactory->create(
            sprintf('creditmemo%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $this->pdfCreditmemo->getPdf($collection)->render(),
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
