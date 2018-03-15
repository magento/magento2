<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryLowQuantityNotification\Block\Adminhtml\Rss;

use Magento\Backend\Block\AbstractBlock;
use Magento\Backend\Block\Context;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock\GetCollection;

class NotifyStock extends AbstractBlock implements DataProviderInterface
{
    /**
     * @var UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @var GetCollection
     */
    protected $getCollection;

    /**
     * @param Context $context
     * @param GetCollection $getCollection
     * @param UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        GetCollection $getCollection,
        UrlBuilderInterface $rssUrlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->getCollection = $getCollection;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setCacheKey('rss_catalog_notifystock');
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getRssData()
    {
        $newUrl = $this->rssUrlBuilder->getUrl(['_secure' => true, '_nosecret' => true, 'type' => 'notifystock']);
        $title = __('Low Stock Products');
        $data = ['title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8'];

        foreach ($this->getCollection->execute() as $item) {
            $url = $this->getUrl(
                'catalog/product/edit',
                ['id' => $item->getId(), '_secure' => true, '_nosecret' => true]
            );
            $qty = 1 * $item->getData('qty');

            $description = __(
                '%1 has reached a quantity of %2 in source %3(Source Code: %4).',
                $item->getData('name'),
                $qty,
                $item->getData('source_name'),
                $item->getData(SourceItemInterface::SOURCE_CODE)
            );

            $data['entries'][] = ['title' => $item->getData('name'), 'link' => $url, 'description' => $description];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheLifetime()
    {
        return 600;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed()
    {
        return true;
    }

    /**
     * {@inheritdoc}
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
