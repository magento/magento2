<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order history tab
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    private const HISTORY_TYPE_INVOICE = 1;
    private const HISTORY_TYPE_INVOICE_COMMENT = 2;
    private const HISTORY_TYPE_ORDER = 3;
    private const HISTORY_TYPE_CREDIT_MEMO = 4;
    private const HISTORY_TYPE_CREDIT_MEMO_COMMENT = 5;
    private const HISTORY_TYPE_SHIPMENT = 6;
    private const HISTORY_TYPE_SHIPMENT_COMMENT = 7;
    private const HISTORY_TYPE_TRACKING_NUM = 8;

    /**
     * History Template
     *
     * @var string
     */
    protected $_template = 'Magento_Sales::order/view/tab/history.phtml';

    /**
     * Core registry variable
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
        $this->adminHelper = $adminHelper;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Compose and get order full history.
     *
     * Consists of the status history comments as well as of invoices, shipments and creditmemos creations
     *
     * @TODO This method requires refactoring. Need to create separate model for comment history handling
     * and avoid generating it dynamically
     *
     * @return array
     */
    public function getFullHistory()
    {
        $order = $this->getOrder();

        $history = [];
        foreach ($order->getAllStatusHistory() as $orderComment) {
            $history[] = $this->prepareHistoryItemWithId(
                $orderComment->getId(),
                $orderComment->getStatusLabel(),
                $orderComment->getIsCustomerNotified() ?? false,
                $this->getOrderAdminDate($orderComment->getCreatedAt()),
                $orderComment->getComment() ?? '',
                self::HISTORY_TYPE_ORDER
            );
        }

        foreach ($order->getCreditmemosCollection() as $_memo) {
            $history[] = $this->prepareHistoryItemWithId(
                $_memo->getId(),
                __('Credit memo #%1 created', $_memo->getIncrementId()),
                $_memo->getEmailSent() ?? false,
                $this->getOrderAdminDate($_memo->getCreatedAt()),
                '',
                self::HISTORY_TYPE_CREDIT_MEMO
            );

            foreach ($_memo->getCommentsCollection() as $_comment) {
                $history[] = $this->prepareHistoryItemWithId(
                    $_comment->getId(),
                    __('Credit memo #%1 comment added', $_memo->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($_comment->getCreatedAt()),
                    $_comment->getComment(),
                    self::HISTORY_TYPE_CREDIT_MEMO_COMMENT
                );
            }
        }

        foreach ($order->getShipmentsCollection() as $_shipment) {
            $history[] = $this->prepareHistoryItemWithId(
                $_shipment->getId(),
                __('Shipment #%1 created', $_shipment->getIncrementId()),
                $_shipment->getEmailSent() ?? false,
                $this->getOrderAdminDate($_shipment->getCreatedAt()),
                '',
                self::HISTORY_TYPE_SHIPMENT
            );

            foreach ($_shipment->getCommentsCollection() as $_comment) {
                $history[] = $this->prepareHistoryItemWithId(
                    $_comment->getId(),
                    __('Shipment #%1 comment added', $_shipment->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($_comment->getCreatedAt()),
                    $_comment->getComment(),
                    self::HISTORY_TYPE_SHIPMENT_COMMENT
                );
            }
        }

        foreach ($order->getInvoiceCollection() as $_invoice) {
            $history[] = $this->prepareHistoryItemWithId(
                $_invoice->getId(),
                __('Invoice #%1 created', $_invoice->getIncrementId()),
                $_invoice->getEmailSent() ?? false,
                $this->getOrderAdminDate($_invoice->getCreatedAt()),
                '',
                self::HISTORY_TYPE_INVOICE
            );

            foreach ($_invoice->getCommentsCollection() as $_comment) {
                $history[] = $this->prepareHistoryItemWithId(
                    $_comment->getId(),
                    __('Invoice #%1 comment added', $_invoice->getIncrementId()),
                    $_comment->getIsCustomerNotified(),
                    $this->getOrderAdminDate($_comment->getCreatedAt()),
                    $_comment->getComment(),
                    self::HISTORY_TYPE_INVOICE_COMMENT
                );
            }
        }

        foreach ($order->getTracksCollection() as $_track) {
            $history[] = $this->prepareHistoryItemWithId(
                $_track->getId(),
                __('Tracking number %1 for %2 assigned', $_track->getNumber(), $_track->getTitle()),
                false,
                $this->getOrderAdminDate($_track->getCreatedAt()),
                '',
                self::HISTORY_TYPE_TRACKING_NUM
            );
        }

        usort($history, [__CLASS__, 'sortHistory']);
        return $history;
    }

    /**
     * Status history date/datetime getter
     *
     * @param array $item
     * @param string $dateType
     * @param int $format
     * @return string
     */
    public function getItemCreatedAt(array $item, $dateType = 'date', $format = \IntlDateFormatter::MEDIUM)
    {
        if (!isset($item['created_at'])) {
            return '';
        }
        if ('date' === $dateType) {
            return $this->formatDate($item['created_at'], $format);
        }
        return $this->formatTime($item['created_at'], $format);
    }

    /**
     * Status history item title getter
     *
     * @param array $item
     * @return string
     */
    public function getItemTitle(array $item)
    {
        return isset($item['title']) ? $this->escapeHtml($item['title']) : '';
    }

    /**
     * Check whether status history comment is with customer notification
     *
     * @param array $item
     * @param bool $isSimpleCheck
     * @return bool
     */
    public function isItemNotified(array $item, $isSimpleCheck = true)
    {
        if ($isSimpleCheck) {
            return !empty($item['notified']);
        }
        return isset($item['notified']) && false !== $item['notified'];
    }

    /**
     * Status history item comment getter
     *
     * @param array $item
     * @return string
     */
    public function getItemComment(array $item)
    {
        $allowedTags = ['b', 'br', 'strong', 'i', 'u', 'a'];
        return isset($item['comment'])
            ? $this->adminHelper->escapeHtmlWithLinks($item['comment'], $allowedTags) : '';
    }

    /**
     * Map history items as array
     *
     * @param string $label
     * @param bool $notified
     * @param \DateTimeInterface $created
     * @param string $comment
     * @return array
     */
    protected function _prepareHistoryItem($label, $notified, $created, $comment = '')
    {
        return ['title' => $label, 'notified' => $notified, 'comment' => $comment, 'created_at' => $created];
    }

    /**
     * Map history items as array with id and type
     *
     * @param int $id
     * @param string $label
     * @param bool $notified
     * @param \DateTimeInterface $created
     * @param string $comment
     * @param int $type
     * @return array
     */
    private function prepareHistoryItemWithId(
        int $id,
        string $label,
        bool $notified,
        \DateTimeInterface $created,
        string $comment = '',
        int $type = 0
    ): array {
        return [
            'entity_id' => $id,
            'title' => $label,
            'notified' => $notified,
            'comment' => $comment,
            'created_at' => $created,
            'type' => $type
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Comments History');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Order History');
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('sales/*/commentsHistory', ['_current' => true]);
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Customer Notification Applicable check method
     *
     * @param array $historyItem
     * @return bool
     */
    public function isCustomerNotificationNotApplicable($historyItem)
    {
        return $historyItem['notified'] ==
            \Magento\Sales\Model\Order\Status\History::CUSTOMER_NOTIFICATION_NOT_APPLICABLE;
    }

    /**
     * Comparison For Sorting History By Timestamp
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     * phpcs:disable
     */
    public static function sortHistoryByTimestamp($a, $b)
    {
        $createdAtA = $a['created_at'];
        $createdAtB = $b['created_at'];

        return $createdAtA->getTimestamp() <=> $createdAtB->getTimestamp();
    }

    /**
     * Comparison For Sorting History By Timestamp and entity_id
     *
     * @param array $a
     * @param array $b
     * @return int
     * @SuppressWarnings("unused")
     * phpcs:enable
     */
    private function sortHistory(array $a, array $b): int
    {
        $result = $this->sortHistoryByTimestamp($a, $b);
        if (0 !== $result) {
            return $result;
        }

        $result = $a['type'] <=> $b['type'];
        if (0 !== $result) {
            return $result;
        }

        return $a['entity_id'] <=> $b['entity_id'];
    }

    /**
     * Get order admin date
     *
     * @param int $createdAt
     * @return \DateTime
     */
    public function getOrderAdminDate($createdAt)
    {
        return $this->_localeDate->date(new \DateTime($createdAt));
    }
}
