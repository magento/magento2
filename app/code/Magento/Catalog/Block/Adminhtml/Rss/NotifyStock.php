<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Class NotifyStock
 * @package Magento\Catalog\Block\Adminhtml\Rss
 */
class NotifyStock extends \Magento\Backend\Block\AbstractBlock implements DataProviderInterface
{
    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\NotifyStock
     */
    protected $rssModel;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Model\Rss\Product\NotifyStock $rssModel
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Model\Rss\Product\NotifyStock $rssModel,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        array $data = []
    ) {
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->rssModel = $rssModel;
        parent::__construct($context, $data);
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

        foreach ($this->rssModel->getProductsCollection() as $item) {
            /* @var $item \Magento\Catalog\Model\Product */
            $url = $this->getUrl(
                'catalog/product/edit',
                ['id' => $item->getId(), '_secure' => true, '_nosecret' => true]
            );
            $qty = 1 * $item->getQty();
            $description = __('%1 has reached a quantity of %2.', $item->getName(), $qty);
            $data['entries'][] = ['title' => $item->getName(), 'link' => $url, 'description' => $description];
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
