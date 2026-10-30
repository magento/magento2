<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Block\Cart;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Checkout\Observer\CatalogRuleSaveAfterObserver;

/**
 * Shopping cart abstract block
 */
class AbstractCart extends \Magento\Framework\View\Element\Template
{
    /**
     * Block alias fallback
     */
    public const DEFAULT_TYPE = 'default';

    /**
     * Session key for last time cart totals were recollected (used with catalog rules cache).
     */
    private const SESSION_KEY_LAST_RECOLLECT_AT = 'last_cart_totals_recollect_at';

    /**
     * @var Quote|null
     */
    protected $_quote = null;

    /**
     * @var array
     */
    protected $_totals;

    /**
     * @var array
     */
    protected $_itemRenders = [];

    /**
     * TODO: MAGETWO-34827: unused object?
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @param \Magento\Framework\App\CacheInterface|null $cache
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = [],
        ?\Magento\Framework\App\CacheInterface $cache = null
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->cache = $cache ?? ObjectManager::getInstance()->get(\Magento\Framework\App\CacheInterface::class);
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve renderer list
     *
     * @return \Magento\Framework\View\Element\RendererList
     */
    protected function _getRendererList()
    {
        return $this->getRendererListName() ? $this->getLayout()->getBlock(
            $this->getRendererListName()
        ) : $this->getChildBlock(
            'renderer.list'
        );
    }

    /**
     * Retrieve item renderer block
     *
     * @param string|null $type
     * @return \Magento\Framework\View\Element\Template
     * @throws \RuntimeException
     */
    public function getItemRenderer($type = null)
    {
        if ($type === null) {
            $type = self::DEFAULT_TYPE;
        }
        $rendererList = $this->_getRendererList();
        if (!$rendererList) {
            throw new \RuntimeException('Renderer list for block "' . $this->getNameInLayout() . '" is not defined');
        }
        $overriddenTemplates = $this->getOverriddenTemplates() ?: [];
        $template = isset($overriddenTemplates[$type]) ? $overriddenTemplates[$type] : $this->getRendererTemplate();
        return $rendererList->getRenderer($type, self::DEFAULT_TYPE, $template);
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->_checkoutSession->getQuote();

            if ($this->_quote->getId() && $this->shouldRecollectTotals()) {
                $existingItemsCount = $this->_quote->getItemsCount();
                $existingItemsQty = $this->_quote->getItemsQty();
                $existingVirtualItemsQty = $this->_quote->getData('virtual_items_qty');
                $this->_quote->setData('totals_collected_flag', false);
                $this->_quote->collectTotals();
                $this->_quote->setItemsCount($existingItemsCount);
                $this->_quote->setItemsQty($existingItemsQty);
                $this->_quote->setData('virtual_items_qty', $existingVirtualItemsQty);
                $this->_checkoutSession->setData(
                    self::SESSION_KEY_LAST_RECOLLECT_AT,
                    $this->cache->load(CatalogRuleSaveAfterObserver::CACHE_KEY_CATALOG_RULES_UPDATED_AT)
                );
            }
        }
        return $this->_quote;
    }

    /**
     * Whether cart totals should be recollected (only after a catalog price rule was saved).
     *
     * @return bool
     */
    private function shouldRecollectTotals(): bool
    {
        $rulesUpdatedAt = (int) ($this->cache->load(
            CatalogRuleSaveAfterObserver::CACHE_KEY_CATALOG_RULES_UPDATED_AT
        ) ?: 0);
        if ($rulesUpdatedAt <= 0) {
            return false;
        }
        $lastRecollectAt = (int) ($this->_checkoutSession->getData(self::SESSION_KEY_LAST_RECOLLECT_AT) ?: 0);
        return $rulesUpdatedAt > $lastRecollectAt;
    }

    /**
     * Get all cart items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Get item row html
     *
     * @param   \Magento\Quote\Model\Quote\Item $item
     * @return  string
     */
    public function getItemHtml(\Magento\Quote\Model\Quote\Item $item)
    {
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }

    /**
     * Retrieve totals.
     *
     * @return array
     */
    public function getTotals()
    {
        return $this->getTotalsCache();
    }

    /**
     * Retrieve cached totals.
     *
     * @return array
     */
    public function getTotalsCache()
    {
        if (empty($this->_totals)) {
            if ($this->getQuote()->isVirtual()) {
                $this->_totals = $this->getQuote()->getBillingAddress()->getTotals();
            } else {
                $this->_totals = $this->getQuote()->getShippingAddress()->getTotals();
            }
        }
        return $this->_totals;
    }
}
