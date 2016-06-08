<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\InvoiceRepository;

abstract class View extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->registry = $registry;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * Invoice information page
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        if ($this->getRequest()->getParam('invoice_id')) {
            $resultForward->setController('order_invoice')
                ->setParams(['come_from' => 'invoice'])
                ->forward('view');
        } else {
            $resultForward->forward('noroute');
        }
        return $resultForward;
    }

    /**
     * @return \Magento\Sales\Model\Order\Invoice|bool
     */
    protected function getInvoice()
    {
        try {
            $invoice = $this->getInvoiceRepository()
                ->get($this->getRequest()->getParam('invoice_id'));
            $this->registry->register('current_invoice', $invoice);
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Invoice capturing error'));
            return false;
        }

        return $invoice;
    }

    /**
     * @return InvoiceRepository
     *
     * @deprecated
     */
    private function getInvoiceRepository()
    {
        if ($this->invoiceRepository === null) {
            $this->invoiceRepository = ObjectManager::getInstance()
                ->get(InvoiceRepositoryInterface::class);
        }

        return $this->invoiceRepository;
    }
}
