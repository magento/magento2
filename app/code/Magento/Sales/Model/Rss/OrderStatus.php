<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Class OrderStatus
 * @package Magento\Sales\Model\Rss
 */
class OrderStatus implements DataProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Rss\OrderStatusFactory
     */
    protected $orderResourceFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Model\ResourceModel\Order\Rss\OrderStatusFactory $orderResourceFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\ResourceModel\Order\Rss\OrderStatusFactory $orderResourceFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->objectManager = $objectManager;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->orderResourceFactory = $orderResourceFactory;
        $this->localeDate = $localeDate;
        $this->orderFactory = $orderFactory;
        $this->config = $scopeConfig;
    }

    /**
     * Check if RSS feed allowed
     *
     * @return bool
     */
    public function isAllowed()
    {
        if ($this->config->getValue('rss/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getRssData()
    {
        $this->order = $this->getOrder();
        if ($this->order === null) {
            throw new \InvalidArgumentException('Order not found.');
        }
        return array_merge($this->getHeader(), $this->getEntries());
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        $order = $this->getOrder();
        $key = '';
        if ($order !== null) {
            $key = md5($order->getId() . $order->getIncrementId() . $order->getCustomerId());
        }
        return 'rss_order_status_data_' . $key;
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        return 600;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        if ($this->order) {
            return $this->order;
        }

        $data = null;
        $json = base64_decode((string)$this->request->getParam('data'));
        if ($json) {
            $data = json_decode($json, true);
        }
        if (!is_array($data)) {
            return null;
        }

        if (!isset($data['order_id']) || !isset($data['increment_id']) || !isset($data['customer_id'])) {
            return null;
        }

        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->orderFactory->create();
        $order->load($data['order_id']);

        if ($order->getIncrementId() !== $data['increment_id'] || $order->getCustomerId() !== $data['customer_id']) {
            $order = null;
        }
        $this->order = $order;

        return $this->order;
    }

    /**
     * Get RSS feed items
     *
     * @return array
     */
    protected function getEntries()
    {
        /** @var $resourceModel \Magento\Sales\Model\ResourceModel\Order\Rss\OrderStatus */
        $resourceModel = $this->orderResourceFactory->create();
        $results = $resourceModel->getAllCommentCollection($this->order->getId());
        $entries = [];
        if ($results) {
            foreach ($results as $result) {
                $urlAppend = 'view';
                $type = $result['entity_type_code'];
                if ($type && $type != 'order') {
                    $urlAppend = $type;
                }
                $type = __(ucwords($type));
                $title = __('Details for %1 #%2', $type, $result['increment_id']);
                $description = '<p>' . __('Notified Date: %1', $this->localeDate->formatDate($result['created_at']))
                    . '<br/>'
                    . __('Comment: %1<br/>', $result['comment']) . '</p>';
                $url = $this->urlBuilder->getUrl(
                    'sales/order/' . $urlAppend,
                    ['order_id' => $this->order->getId()]
                );
                $entries[] = ['title' => $title, 'link' => $url, 'description' => $description];
            }
        }
        $title = __('Order #%1 created at %2', $this->order->getIncrementId(), $this->localeDate->formatDate(
            $this->order->getCreatedAt()
        ));
        $url = $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $this->order->getId()]);
        $description = '<p>' . __('Current Status: %1<br/>', $this->order->getStatusLabel()) .
            __('Total: %1<br/>', $this->order->formatPrice($this->order->getGrandTotal())) . '</p>';

        $entries[] = ['title' => $title, 'link' => $url, 'description' => $description];

        return ['entries' => $entries];
    }

    /**
     * Get data for Header esction of RSS feed
     *
     * @return array
     */
    protected function getHeader()
    {
        $title = __('Order # %1 Notification(s)', $this->order->getIncrementId());
        $newUrl = $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $this->order->getId()]);

        return ['title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8'];
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthRequired()
    {
        return true;
    }
}
