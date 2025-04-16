<?php
/************************************************************************
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Sales\Model\Order\Invoice\Comment as InvoiceComment;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Comment as InvoiceCommentResource;

/**
 * Add invoice comment
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddComment extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View implements
    HttpPostActionInterface
{
    /**
     * @var InvoiceCommentSender
     */
    protected $invoiceCommentSender;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var InvoiceComment
     */
    private $invoiceComment;

    /**
     * @var InvoiceCommentResource
     */
    private $invoiceCommentResource;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param InvoiceCommentSender $invoiceCommentSender
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param InvoiceRepositoryInterface|null $invoiceRepository
     * @param InvoiceComment|null $invoiceComment
     * @param InvoiceCommentResource|null $invoiceCommentResource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        InvoiceCommentSender $invoiceCommentSender,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        ?InvoiceRepositoryInterface $invoiceRepository = null,
        ?InvoiceComment $invoiceComment = null,
        ?InvoiceCommentResource $invoiceCommentResource = null
    ) {
        $this->invoiceCommentSender = $invoiceCommentSender;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->invoiceRepository = $invoiceRepository ?:
            ObjectManager::getInstance()->get(InvoiceRepositoryInterface::class);
        $this->invoiceComment = $invoiceComment ?? ObjectManager::getInstance()->get(InvoiceComment::class);
        $this->invoiceCommentResource = $invoiceCommentResource ??
            ObjectManager::getInstance()->get(InvoiceCommentResource::class);

        parent::__construct($context, $registry, $resultForwardFactory, $invoiceRepository);
    }

    /**
     * Add comment to invoice action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $this->getRequest()->setParam('invoice_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new LocalizedException(__('The comment is missing. Enter and try again.'));
            }
            $invoice = $this->getInvoice();

            if (!$invoice) {
                /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
                $resultForward = $this->resultForwardFactory->create();
                return $resultForward->forward('noroute');
            }

            if (empty($data['comment_id'])) {
                $invoice->addComment(
                    $data['comment'],
                    isset($data['is_customer_notified']),
                    isset($data['is_visible_on_front'])
                );

                $this->invoiceCommentSender->send($invoice, !empty($data['is_customer_notified']), $data['comment']);
                $this->invoiceRepository->save($invoice);
            } else {
                $comment = $this->invoiceComment->setComment($data['comment'])->setId($data['comment_id']);
                $comment->setInvoice($invoice);
                $this->invoiceCommentResource->save($comment);
            }

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
            $response = $resultPage->getLayout()->getBlock('invoice_comments')->toHtml();
        } catch (LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
        if (is_array($response)) {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($response);
            return $resultJson;
        } else {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setContents($response);
            return $resultRaw;
        }
    }
}
