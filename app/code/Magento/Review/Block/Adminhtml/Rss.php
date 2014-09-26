<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\Review
     */
    protected $rssModel;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\Rss $rssModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Review\Model\Rss $rssModel,
        array $data = array()
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
        $newUrl = $this->getUrl('rss/catalog/review', array('_secure' => true, '_nosecret' => true));
        $title = __('Pending product review(s)');

        $data = array('title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8');

        foreach ($this->rssModel->getProductCollection() as $item) {
            if ($item->getStoreId()) {
                $this->_urlBuilder->setScope($item->getStoreId());
            }

            $url = $this->getUrl('catalog/product/view', array('id' => $item->getId()));
            $reviewUrl = $this->getUrl('review/product/edit/', array(
                'id' => $item->getReviewId(),
                '_secure' => true,
                '_nosecret' => true
            ));

            $storeName = $this->storeManager->getStore($item->getStoreId())->getName();
            $description = '<p>' . __('Product: <a href="%1" target="_blank">%2</a> <br/>', $url, $item->getName())
                . __('Summary of review: %1 <br/>', $item->getTitle()) . __('Review: %1 <br/>', $item->getDetail())
                . __('Store: %1 <br/>', $storeName)
                . __('Click <a href="%1">here</a> to view the review.', $reviewUrl)
                . '</p>';

            $data['entries'][] = array(
                'title' => __('Product: "%1" reviewed by: %2', $item->getName(), $item->getNickname()),
                'link' => $item->getProductUrl(),
                'description' => $description
            );
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
        return array();
    }
}
