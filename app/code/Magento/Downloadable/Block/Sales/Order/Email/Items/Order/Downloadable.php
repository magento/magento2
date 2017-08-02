<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Downloadable\Block\Sales\Order\Email\Items\Order;

use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Downloadable Sales Order Email items renderer
 *
 * @api
 * @since 2.0.0
 */
class Downloadable extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * @var \Magento\Downloadable\Model\Link\Purchased
     * @since 2.0.0
     */
    protected $_purchased;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     * @since 2.0.0
     */
    protected $_purchasedFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     * @since 2.0.0
     */
    protected $_itemsFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.1.0
     */
    private $frontendUrlBuilder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory,
        array $data = []
    ) {
        $this->_purchasedFactory = $purchasedFactory;
        $this->_itemsFactory = $itemsFactory;
        parent::__construct($context, $data);
    }

    /**
     * Enter description here...
     *
     * @return \Magento\Downloadable\Model\Link\Purchased
     * @since 2.0.0
     */
    public function getLinks()
    {
        $this->_purchased = $this->_purchasedFactory->create()->load(
            $this->getItem()->getId(),
            'order_item_id'
        );
        $purchasedLinks = $this->_itemsFactory->create()->addFieldToFilter('order_item_id', $this->getItem()->getId());
        $this->_purchased->setPurchasedItems($purchasedLinks);

        return $this->_purchased;
    }

    /**
     * @return null|string
     * @since 2.0.0
     */
    public function getLinksTitle()
    {
        return $this->getLinks()->getLinkSectionTitle() ?: $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param Item $item
     * @return string
     * @since 2.0.0
     */
    public function getPurchasedLinkUrl($item)
    {
        $url = $this->getFrontendUrlBuilder()->getUrl(
            'downloadable/download/link',
            [
                'id' => $item->getLinkHash(),
                '_scope' => $this->getOrder()->getStore(),
                '_secure' => true,
                '_nosid' => true
            ]
        );
        return $url;
    }

    /**
     * Get frontend URL builder
     *
     * @return \Magento\Framework\UrlInterface
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getFrontendUrlBuilder()
    {
        if (!$this->frontendUrlBuilder) {
            $this->frontendUrlBuilder = ObjectManager::getInstance()->get(\Magento\Framework\Url::class);
        }
        return $this->frontendUrlBuilder;
    }
}
