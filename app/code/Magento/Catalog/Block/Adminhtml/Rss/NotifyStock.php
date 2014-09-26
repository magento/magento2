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
        array $data = array()
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
        $newUrl = $this->rssUrlBuilder->getUrl(array('_secure' => true, '_nosecret' => true, 'type' => 'notifystock'));
        $title = __('Low Stock Products');
        $data = array('title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8');

        foreach ($this->rssModel->getProductsCollection() as $item) {
            /* @var $item \Magento\Catalog\Model\Product */
            $url = $this->getUrl(
                'catalog/product/edit',
                array('id' => $item->getId(), '_secure' => true, '_nosecret' => true)
            );
            $qty = 1 * $item->getQty();
            $description = __('%1 has reached a quantity of %2.', $item->getName(), $qty);
            $data['entries'][] = array('title' => $item->getName(), 'link' => $url, 'description' => $description);
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
        return array();
    }
}
