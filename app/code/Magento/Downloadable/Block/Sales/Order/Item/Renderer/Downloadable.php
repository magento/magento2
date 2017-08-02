<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Downloadable\Block\Sales\Order\Item\Renderer;

use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Store\Model\ScopeInterface;

/**
 * Downloadable order item render block
 *
 * @api
 * @since 2.0.0
 */
class Downloadable extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    /**
     * @var Purchased
     * @since 2.0.0
     */
    protected $_purchasedLinks;

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
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory,
        array $data = []
    ) {
        $this->_purchasedFactory = $purchasedFactory;
        $this->_itemsFactory = $itemsFactory;
        parent::__construct($context, $string, $productOptionFactory, $data);
    }

    /**
     * @return Purchased
     * @since 2.0.0
     */
    public function getLinks()
    {
        $this->_purchasedLinks = $this->_purchasedFactory->create()->load(
            $this->getOrderItem()->getId(),
            'order_item_id'
        );
        $purchasedItems = $this->_itemsFactory->create()->addFieldToFilter(
            'order_item_id',
            $this->getOrderItem()->getId()
        );
        $this->_purchasedLinks->setPurchasedItems($purchasedItems);

        return $this->_purchasedLinks;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getLinksTitle()
    {
        return $this->getLinks()->getLinkSectionTitle() ?: $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
