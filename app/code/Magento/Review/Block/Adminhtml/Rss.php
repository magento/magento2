<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml;

use Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Class Rss
 * @package Magento\Catalog\Block\Adminhtml\Rss
 */
class Rss extends \Magento\Backend\Block\AbstractBlock implements DataProviderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\Review
     */
    protected $rssModel;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\Rss $rssModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\Rss $rssModel,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->rssModel = $rssModel;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getRssData()
    {
        $newUrl = $this->getUrl('rss/catalog/review', ['_secure' => true, '_nosecret' => true]);
        $title = __('Pending product review(s)');

        $data = ['title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8'];

        foreach ($this->rssModel->getProductCollection() as $item) {
            if ($item->getStoreId()) {
                $this->_urlBuilder->setScope($item->getStoreId());
            }

            $url = $this->getUrl('catalog/product/view', ['id' => $item->getId()]);
            $reviewUrl = $this->getUrl('review/product/edit/', [
                'id' => $item->getReviewId(),
                '_secure' => true,
                '_nosecret' => true
            ]);

            $storeName = $this->storeManager->getStore($item->getStoreId())->getName();
            $description = '<p>' . __('Product: <a href="%1" target="_blank">%2</a> <br/>', $url, $item->getName())
                . __('Summary of review: %1 <br/>', $item->getTitle()) . __('Review: %1 <br/>', $item->getDetail())
                . __('Store: %1 <br/>', $storeName)
                . __('Click <a href="%1">here</a> to view the review.', $reviewUrl)
                . '</p>';

            $data['entries'][] = [
                'title' => __('Product: "%1" reviewed by: %2', $item->getName(), $item->getNickname()),
                'link' => $item->getProductUrl(),
                'description' => $description,
            ];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheLifetime()
    {
        return 0;
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
