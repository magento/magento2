<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Rss;

use Magento\Backend\Block\AbstractBlock;
use Magento\Backend\Block\Context;
use Magento\Catalog\Block\Adminhtml\Rss\NotifyStock\DescriptionProvider;
use Magento\Catalog\Model\Rss\Product\NotifyStock as RssNotifyStock;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;

/**
 * Class NotifyStock
 * @package Magento\Catalog\Block\Adminhtml\Rss
 */
class NotifyStock extends AbstractBlock implements DataProviderInterface
{
    /**
     * @var UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @var RssNotifyStock
     */
    protected $rssModel;

    /**
     * @var DescriptionProvider
     */
    private $descriptionProvider;

    /**
     * @param Context $context
     * @param RssNotifyStock $rssModel
     * @param UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     * @param DescriptionProvider|null $descriptionProvider
     */
    public function __construct(
        Context $context,
        RssNotifyStock $rssModel,
        UrlBuilderInterface $rssUrlBuilder,
        array $data = [],
        DescriptionProvider $descriptionProvider = null
    ) {
        parent::__construct($context, $data);

        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->rssModel = $rssModel;
        $this->descriptionProvider = $descriptionProvider
            ?: ObjectManager::getInstance()->get(DescriptionProvider::class);
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

        foreach ($this->rssModel->getItemsCollection() as $item) {
            $url = $this->getUrl(
                'catalog/product/edit',
                ['id' => $item->getId(), '_secure' => true, '_nosecret' => true]
            );
            $description = $this->descriptionProvider->execute($item);
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
