<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Model\ResourceModel\GridPool;
use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;

/**
 * Class AddComment
 *
 * Controller responsible for addition of the order comment to the order
 */
class AddComment extends \Magento\Sales\Controller\Adminhtml\Order implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::comment';

    /**
     * ACL resource needed to send comment email notification
     */
    const ADMIN_SALES_EMAIL_RESOURCE = 'Magento_Sales::emails';

    /**
     * @var OrderCommentSender
     */
    private $orderCommentSender;

    /**
     * @var GridPool
     */
    private $gridPool;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param InlineInterface $translateInline
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param OrderCommentSender $orderCommentSender
     * @param GridPool $gridPool
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        InlineInterface $translateInline,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        ?OrderCommentSender $orderCommentSender = null,
        ?GridPool $gridPool = null
    ) {
        $this->orderCommentSender = $orderCommentSender ?? ObjectManager::getInstance()->get(OrderCommentSender::class);
        $this->gridPool = $gridPool ?? ObjectManager::getInstance()->get(GridPool::class);
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
    }

    /**
     * Add order comment action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $data = $this->getRequest()->getPost('history');
                if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The comment is missing. Enter and try again.')
                    );
                }

                $order->setStatus($data['status']);
                $notify = $data['is_customer_notified'] ?? false;
                $visible = $data['is_visible_on_front'] ?? false;

                if ($notify && !$this->_authorization->isAllowed(self::ADMIN_SALES_EMAIL_RESOURCE)) {
                    $notify = false;
                }

                $history = $order->addStatusHistoryComment($data['comment'], $data['status']);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                $history->save();

                $comment = trim(strip_tags($data['comment']));

                $order->save();
                $this->orderCommentSender->send($order, $notify, $comment);
                $this->gridPool->refreshByOrderId($order->getId());

                return $this->resultPageFactory->create();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot add order history.')];
            }
            if (is_array($response)) {
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setData($response);
                return $resultJson;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }
}
