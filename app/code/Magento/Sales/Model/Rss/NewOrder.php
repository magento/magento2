<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Class NewOrder
 * @package Magento\Sales\Model\Rss
 */
class NewOrder implements DataProviderInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * System event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Parent layout of the block
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->orderFactory = $orderFactory;
        $this->urlBuilder = $urlBuilder;
        $this->localeDate = $localeDate;
        $this->dateTime = $dateTime;
        $this->eventManager = $eventManager;
        $this->layout = $layout;
        $this->rssUrlBuilder = $rssUrlBuilder;
    }

    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     */
    public function isAllowed()
    {
        return true;
    }

    /**
     * Get RSS feed items
     *
     * @return array
     */
    public function getRssData()
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $interval = new \DateInterval('P7D');
        $dateTime->sub($interval);
        $fromDate = $this->dateTime->formatDate($dateTime->getTimestamp());
        $newUrl = $this->rssUrlBuilder->getUrl(['_secure' => true, '_nosecret' => true, 'type' => 'new_order']);
        $title = __('New Orders');
        $data = ['title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8'];

        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->orderFactory->create();
        /** @var $collection \Magento\Sales\Model\ResourceModel\Order\Collection */
        $collection = $order->getResourceCollection();
        $collection->addAttributeToFilter('created_at', ['date' => true, 'from' => $fromDate])
            ->addAttributeToSort('created_at', 'desc');
        $this->eventManager->dispatch('rss_order_new_collection_select', ['collection' => $collection]);

        $detailBlock = $this->layout->getBlockSingleton(\Magento\Sales\Block\Adminhtml\Order\Details::class);
        foreach ($collection as $item) {
            $title = __('Order #%1 created at %2', $item->getIncrementId(), $this->localeDate->formatDate(
                $item->getCreatedAt()
            ));
            $url = $this->urlBuilder->getUrl(
                'sales/order/view',
                ['_secure' => true, 'order_id' => $item->getId(), '_nosecret' => true]
            );
            $detailBlock->setOrder($item);

            $data['entries'][] = (['title' => $title, 'link' => $url, 'description' => $detailBlock->toHtml()]);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return 'rss_new_orders_data';
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        return 60;
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
