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

namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;
use Magento\Sales\Model\Order\Creditmemo\Comment as CreditmemoComment;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment as CreditmemoCommentResource;

class AddComment extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @var CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var CreditmemoCommentSender
     */
    protected $creditmemoCommentSender;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var CreditmemoComment
     */
    private $creditmemoComment;

    /**
     * @var CreditmemoCommentResource
     */
    private $creditmemoCommentResource;

    /**
     * @param Context $context
     * @param CreditmemoLoader $creditmemoLoader
     * @param CreditmemoCommentSender $creditmemoCommentSender
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param RawFactory $resultRawFactory
     * @param CreditmemoComment|null $creditmemoComment
     * @param CreditmemoCommentResource|null $creditmemoCommentResource
     */
    public function __construct(
        Action\Context $context,
        CreditmemoLoader $creditmemoLoader,
        CreditmemoCommentSender $creditmemoCommentSender,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        ?CreditmemoComment $creditmemoComment = null,
        ?CreditmemoCommentResource  $creditmemoCommentResource = null
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->creditmemoCommentSender = $creditmemoCommentSender;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->creditmemoComment = $creditmemoComment ??
            ObjectManager::getInstance()->get(CreditmemoComment::class);
        $this->creditmemoCommentResource = $creditmemoCommentResource ??
            ObjectManager::getInstance()->get(CreditmemoCommentResource::class);
        parent::__construct($context);
    }

    /**
     * Add comment to creditmemo history
     *
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->getRequest()->setParam('creditmemo_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new LocalizedException(
                    __('The comment is missing. Enter and try again.')
                );
            }
            $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
            $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
            $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
            $creditmemo = $this->creditmemoLoader->load();

            if (empty($data['comment_id'])) {
                $comment = $creditmemo->addComment(
                    $data['comment'],
                    isset($data['is_customer_notified']),
                    isset($data['is_visible_on_front'])
                );
                $this->creditmemoCommentSender->send(
                    $creditmemo,
                    !empty($data['is_customer_notified']),
                    $data['comment']
                );
                $comment->save();
            } else {
                $comment = $this->creditmemoComment->setComment($data['comment'])->setId($data['comment_id']);
                $comment->setCreditmemo($creditmemo);
                $this->creditmemoCommentResource->save($comment);
            }

            $resultPage = $this->resultPageFactory->create();
            $response = $resultPage->getLayout()->getBlock('creditmemo_comments')->toHtml();
        } catch (LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
        if (is_array($response)) {
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($response);
            return $resultJson;
        } else {
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setContents($response);
            return $resultRaw;
        }
    }
}
