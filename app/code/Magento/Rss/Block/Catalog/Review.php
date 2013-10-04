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
 * @category    Magento
 * @package     Magento_Rss
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review form block
 */
namespace Magento\Rss\Block\Catalog;

class Review extends \Magento\Core\Block\AbstractBlock
{
    /**
     * Rss data
     *
     * @var \Magento\Rss\Helper\Data
     */
    protected $_rssData = null;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Rss\Model\RssFactory
     */
    protected $_rssFactory;

    /**
     * @var \Magento\Core\Model\Resource\Iterator
     */
    protected $_resourceIterator;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Rss\Helper\Data $rssData
     * @param \Magento\Core\Block\Context $context
     * @param \Magento\Rss\Model\RssFactory $rssFactory
     * @param \Magento\Core\Model\Resource\Iterator $resourceIterator
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Rss\Helper\Data $rssData,
        \Magento\Core\Block\Context $context,
        \Magento\Rss\Model\RssFactory $rssFactory,
        \Magento\Core\Model\Resource\Iterator $resourceIterator,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->_rssData = $rssData;
        $this->_rssFactory = $rssFactory;
        $this->_resourceIterator = $resourceIterator;
        $this->_reviewFactory = $reviewFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Render XML response
     *
     * @return string
     */
    protected function _toHtml()
    {
        $newUrl = $this->_urlBuilder->getUrl('rss/catalog/review');
        $title = __('Pending product review(s)');
        $this->_rssData->disableFlat();

        /** @var $rssObj \Magento\Rss\Model\Rss */
        $rssObj = $this->_rssFactory->create();
        $rssObj->_addHeader(array(
            'title' => $title,
            'description' => $title,
            'link'        => $newUrl,
            'charset'     => 'UTF-8',
        ));

        /** @var $reviewModel \Magento\Review\Model\Review */
        $reviewModel = $this->_reviewFactory->create();
        $collection = $reviewModel->getProductCollection()
            ->addStatusFilter($reviewModel->getPendingStatus())
            ->addAttributeToSelect('name', 'inner')
            ->setDateOrder();

        $this->_eventManager->dispatch('rss_catalog_review_collection_select', array('collection' => $collection));

        $this->_resourceIterator->walk(
            $collection->getSelect(),
            array(array($this, 'addReviewItemXmlCallback')),
            array('rssObj' => $rssObj, 'reviewModel' => $reviewModel)
        );
        return $rssObj->createRssXml();
    }

    /**
     * Format single RSS element
     *
     * @param array $args
     * @return null
     */
    public function addReviewItemXmlCallback($args)
    {
        /** @var $rssObj \Magento\Rss\Model\Rss */
        $rssObj = $args['rssObj'];
        $row = $args['row'];

        $store = $this->_storeManager->getStore($row['store_id']);
        $productUrl = $store->getUrl('catalog/product/view', array('id' => $row['entity_id']));
        $reviewUrl = $this->_adminhtmlData->getUrl(
            'adminhtml/catalog_product_review/edit/',
            array('id' => $row['review_id'], '_secure' => true, '_nosecret' => true)
        );
        $storeName = $store->getName();
        $description = '<p>'
             . __('Product: <a href="%1">%2</a> <br/>', $productUrl, $row['name'])
             . __('Summary of review: %1 <br/>', $row['title'])
             . __('Review: %1 <br/>', $row['detail'])
             . __('Store: %1 <br/>', $storeName )
             . __('Click <a href="%1">here</a> to view the review.', $reviewUrl)
             . '</p>';
        $rssObj->_addEntry(array(
            'title'       => __('Product: "%1" review By: %2', $row['name'], $row['nickname']),
            'link'        => 'test',
            'description' => $description,
        ));
    }
}
