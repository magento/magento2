<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Block\Sales\Order\Item\Renderer;

use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Downloadable order item render block
 *
 * @api
 * @since 100.0.2
 */
class Downloadable extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{
    /**
     * @var Purchased
     */
    protected $_purchasedLinks;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $_purchasedFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     */
    protected $_itemsFactory;
    /**
     * @var \Magento\Downloadable\Model\Sales\Order\Link\Purchased
     */
    private $purchasedLink;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory
     * @param array $data
     * @param \Magento\Downloadable\Model\Sales\Order\Link\Purchased|null $purchasedLink
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory,
        array $data = [],
        ?\Magento\Downloadable\Model\Sales\Order\Link\Purchased $purchasedLink = null
    ) {
        $this->_purchasedFactory = $purchasedFactory;
        $this->_itemsFactory = $itemsFactory;
        parent::__construct($context, $string, $productOptionFactory, $data);
        $this->purchasedLink = $purchasedLink
            ?? ObjectManager::getInstance()->get(\Magento\Downloadable\Model\Sales\Order\Link\Purchased::class);
    }

    /**
     * Get purchased link
     *
     * @return Purchased
     */
    public function getLinks()
    {
        $this->_purchasedLinks = $this->purchasedLink->getLink($this->getOrderItem());

        return $this->_purchasedLinks;
    }

    /**
     * Get purchased link title
     *
     * @return string
     */
    public function getLinksTitle()
    {
        return $this->getLinks()->getLinkSectionTitle() ?: $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
