<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Items;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Adminhtml sales order create items grid block
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Grid extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Flag to check can items be move to customer storage
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_moveToCustomerStorage = true;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     * @since 2.0.0
     */
    protected $_taxData;

    /**
     * Wishlist factory
     *
     * @var \Magento\Wishlist\Model\WishlistFactory
     * @since 2.0.0
     */
    protected $_wishlistFactory;

    /**
     * Gift message save
     *
     * @var \Magento\GiftMessage\Model\Save
     * @since 2.0.0
     */
    protected $_giftMessageSave;

    /**
     * Tax config
     *
     * @var \Magento\Tax\Model\Config
     * @since 2.0.0
     */
    protected $_taxConfig;

    /**
     * Message helper
     *
     * @var \Magento\GiftMessage\Helper\Message
     * @since 2.0.0
     */
    protected $_messageHelper;

    /**
     * @var StockRegistryInterface
     * @since 2.0.0
     */
    protected $stockRegistry;

    /**
     * @var StockStateInterface
     * @since 2.0.0
     */
    protected $stockState;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\GiftMessage\Model\Save $giftMessageSave
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param StockRegistryInterface $stockRegistry
     * @param StockStateInterface $stockState
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\GiftMessage\Model\Save $giftMessageSave,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState,
        array $data = []
    ) {
        $this->_messageHelper = $messageHelper;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_giftMessageSave = $giftMessageSave;
        $this->_taxConfig = $taxConfig;
        $this->_taxData = $taxData;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_search_grid');
    }

    /**
     * Get items
     *
     * @return Item[]
     * @since 2.0.0
     */
    public function getItems()
    {
        $items = $this->getParentBlock()->getItems();
        $oldSuperMode = $this->getQuote()->getIsSuperMode();
        $this->getQuote()->setIsSuperMode(false);
        foreach ($items as $item) {
            // To dispatch inventory event sales_quote_item_qty_set_after, set item qty
            $item->setQty($item->getQty());

            if (!$item->getMessage()) {
                //Getting product ids for stock item last quantity validation before grid display
                $stockItemToCheck = [];

                $childItems = $item->getChildren();
                if (count($childItems)) {
                    foreach ($childItems as $childItem) {
                        $stockItemToCheck[] = $childItem->getProduct()->getId();
                    }
                } else {
                    $stockItemToCheck[] = $item->getProduct()->getId();
                }

                foreach ($stockItemToCheck as $productId) {
                    $check = $this->stockState->checkQuoteItemQty(
                        $productId,
                        $item->getQty(),
                        $item->getQty(),
                        $item->getQty(),
                        $this->getQuote()->getStore()->getWebsiteId()
                    );
                    $item->setMessage($check->getMessage());
                    $item->setHasError($check->getHasError());
                }
            }

            if ($item->getProduct()->getStatus() == ProductStatus::STATUS_DISABLED) {
                $item->setMessage(__('This product is disabled.'));
                $item->setHasError(true);
            }
        }
        $this->getQuote()->setIsSuperMode($oldSuperMode);
        return $items;
    }

    /**
     * Get session
     *
     * @return SessionManagerInterface
     * @since 2.0.0
     */
    public function getSession()
    {
        return $this->getParentBlock()->getSession();
    }

    /**
     * Get item editable price
     *
     * @param Item $item
     * @return float
     * @since 2.0.0
     */
    public function getItemEditablePrice($item)
    {
        return $item->getCalculationPrice() * 1;
    }

    /**
     * Get original editable price
     *
     * @param Item $item
     * @return float
     * @since 2.0.0
     */
    public function getOriginalEditablePrice($item)
    {
        if ($item->hasOriginalCustomPrice()) {
            $result = $item->getOriginalCustomPrice() * 1;
        } elseif ($item->hasCustomPrice()) {
            $result = $item->getCustomPrice() * 1;
        } else {
            if ($this->_taxData->priceIncludesTax($this->getStore())) {
                $result = $item->getPriceInclTax() * 1;
            } else {
                $result = $item->getOriginalPrice() * 1;
            }
        }
        return $result;
    }

    /**
     * Get item original price
     *
     * @param Item $item
     * @return float
     * @since 2.0.0
     */
    public function getItemOrigPrice($item)
    {
        return $this->convertPrice($item->getPrice());
    }

    /**
     * Check gift messages availability
     *
     * @param Item|null $item
     * @return bool|null|string
     * @since 2.0.0
     */
    public function isGiftMessagesAvailable($item = null)
    {
        if ($item === null) {
            return $this->_messageHelper->isMessagesAllowed('items', $this->getQuote(), $this->getStore());
        }
        return $this->_messageHelper->isMessagesAllowed('item', $item, $this->getStore());
    }

    /**
     * Check if allowed for gift message
     *
     * @param Item $item
     * @return bool
     * @since 2.0.0
     */
    public function isAllowedForGiftMessage($item)
    {
        return $this->_giftMessageSave->getIsAllowedQuoteItem($item);
    }

    /**
     * Check if we need display grid totals include tax
     *
     * @return bool
     * @since 2.0.0
     */
    public function displayTotalsIncludeTax()
    {
        $result = $this->_taxConfig->displayCartSubtotalInclTax($this->getStore())
            || $this->_taxConfig->displayCartSubtotalBoth($this->getStore());
        return $result;
    }

    /**
     * Get subtotal
     *
     * @return false|float
     * @since 2.0.0
     */
    public function getSubtotal()
    {
        $address = $this->getQuoteAddress();
        if (!$this->displayTotalsIncludeTax()) {
            return $address->getSubtotal();
        }
        if ($address->getSubtotalInclTax()) {
            return $address->getSubtotalInclTax();
        }
        return $address->getSubtotal() + $address->getTaxAmount();
    }

    /**
     * Get subtotal with discount
     *
     * @return float
     * @since 2.0.0
     */
    public function getSubtotalWithDiscount()
    {
        $address = $this->getQuoteAddress();
        if ($this->displayTotalsIncludeTax()) {
            return $address->getSubtotal()
                + $address->getTaxAmount()
                + $address->getDiscountAmount()
                + $address->getDiscountTaxCompensationAmount();
        } else {
            return $address->getSubtotal() + $address->getDiscountAmount();
        }
    }

    /**
     * Get discount amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getDiscountAmount()
    {
        return $this->getQuote()->getShippingAddress()->getDiscountAmount();
    }

    /**
     * Retrieve quote address
     *
     * @return \Magento\Quote\Model\Quote\Address
     * @since 2.0.0
     */
    public function getQuoteAddress()
    {
        if ($this->getQuote()->isVirtual()) {
            return $this->getQuote()->getBillingAddress();
        } else {
            return $this->getQuote()->getShippingAddress();
        }
    }

    /**
     * Define if specified item has already applied custom price
     *
     * @param Item $item
     * @return bool
     * @since 2.0.0
     */
    public function usedCustomPriceForItem($item)
    {
        return $item->hasCustomPrice();
    }

    /**
     * Define if custom price can be applied for specified item
     *
     * @param Item $item
     * @return bool
     * @since 2.0.0
     */
    public function canApplyCustomPrice($item)
    {
        return !$item->isChildrenCalculated();
    }

    /**
     * Get qty title
     *
     * @param Item $item
     * @return \Magento\Framework\Phrase|string
     * @since 2.0.0
     */
    public function getQtyTitle($item)
    {
        $prices = $item->getProduct()
            ->getPriceInfo()
            ->getPrice(\Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE)
            ->getTierPriceList();
        if ($prices) {
            $info = [];
            foreach ($prices as $data) {
                $price = $this->convertPrice($data['price']);
                $info[] = __('Buy %1 for price %2', $data['price_qty'], $price);
            }
            return implode(', ', $info);
        } else {
            return __('Item ordered qty');
        }
    }

    /**
     * Get tier price html
     *
     * @param Item $item
     * @return string
     * @since 2.0.0
     */
    public function getTierHtml($item)
    {
        $html = '';
        $prices = $item->getProduct()->getTierPrice();
        if ($prices) {
            if ($item->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $info = $this->_getBundleTierPriceInfo($prices);
            } else {
                $info = $this->_getTierPriceInfo($prices);
            }

            $html = implode('<br />', $info);
        }
        return $html;
    }

    /**
     * Get tier price info to display in grid for Bundle product
     *
     * @param array $prices
     * @return string[]
     * @since 2.0.0
     */
    protected function _getBundleTierPriceInfo($prices)
    {
        $info = [];
        foreach ($prices as $data) {
            $qty = $data['price_qty'] * 1;
            $info[] = __('%1 with %2 discount each', $qty, $data['price'] * 1 . '%');
        }
        return $info;
    }

    /**
     * Get tier price info to display in grid
     *
     * @param array $prices
     * @return string[]
     * @since 2.0.0
     */
    protected function _getTierPriceInfo($prices)
    {
        $info = [];
        foreach ($prices as $data) {
            $qty = $data['price_qty'] * 1;
            $price = $this->convertPrice($data['price']);
            $info[] = __('%1 for %2', $qty, $price);
        }
        return $info;
    }

    /**
     * Get Custom Options of item
     *
     * @param Item $item
     * @return string
     *
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    public function getCustomOptions(Item $item)
    {
        $optionStr = '';
        $this->_moveToCustomerStorage = true;
        if ($optionIds = $item->getOptionByCode('option_ids')) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $item->getProduct()->getOptionById($optionId);
                if ($option) {
                    $optionStr .= $option->getTitle() . ':';
                    $quoteItemOption = $item->getOptionByCode('option_' . $option->getId());
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setQuoteItemOption($quoteItemOption);

                    $optionStr .= $group->getEditableOptionValue($quoteItemOption->getValue());
                    $optionStr .= "\n";
                }
            }
        }
        return $optionStr;
    }

    /**
     * Get flag for rights to move items to customer storage
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getMoveToCustomerStorage()
    {
        return $this->_moveToCustomerStorage;
    }

    /**
     * Display subtotal including tax
     *
     * @param Item $item
     * @return string
     * @since 2.0.0
     */
    public function displaySubtotalInclTax($item)
    {
        if ($item->getTaxBeforeDiscount()) {
            $tax = $item->getTaxBeforeDiscount();
        } else {
            $tax = $item->getTaxAmount() ? $item->getTaxAmount() : 0;
        }
        return $this->formatPrice($item->getRowTotal() + $tax);
    }

    /**
     * Display original price including tax
     *
     * @param Item $item
     * @return float
     * @since 2.0.0
     */
    public function displayOriginalPriceInclTax($item)
    {
        $tax = 0;
        if ($item->getTaxPercent()) {
            $tax = $item->getPrice() * ($item->getTaxPercent() / 100);
        }
        return $this->convertPrice($item->getPrice() + $tax / $item->getQty());
    }

    /**
     * Display row total with discount including tax
     *
     * @param Item $item
     * @return string
     * @since 2.0.0
     */
    public function displayRowTotalWithDiscountInclTax($item)
    {
        $tax = $item->getTaxAmount() ? $item->getTaxAmount() : 0;
        return $this->formatPrice($item->getRowTotal() - $item->getDiscountAmount() + $tax);
    }

    /**
     * Get including/excluding tax message
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getInclExclTaxMessage()
    {
        if ($this->_taxData->priceIncludesTax($this->getStore())) {
            return __('* - Enter custom price including tax');
        } else {
            return __('* - Enter custom price excluding tax');
        }
    }

    /**
     * Get store
     *
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
     */
    public function getStore()
    {
        return $this->getQuote()->getStore();
    }

    /**
     * Return html button which calls configure window
     *
     * @param Item $item
     * @return string
     * @since 2.0.0
     */
    public function getConfigureButtonHtml($item)
    {
        $product = $item->getProduct();

        $options = ['label' => __('Configure')];
        if ($product->canConfigure()) {
            $options['onclick'] = sprintf('order.showQuoteItemConfiguration(%s)', $item->getId());
        } else {
            $options['class'] = ' disabled';
            $options['title'] = __('This product does not have any configurable options');
        }

        return $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData($options)->toHtml();
    }

    /**
     * Get order item extra info block
     *
     * @param Item $item
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    public function getItemExtraInfo($item)
    {
        return $this->getLayout()->getBlock('order_item_extra_info')->setItem($item);
    }

    /**
     * Returns whether moving to wishlist is allowed for this item
     *
     * @param Item $item
     * @return bool
     * @since 2.0.0
     */
    public function isMoveToWishlistAllowed($item)
    {
        return $item->getProduct()->isVisibleInSiteVisibility();
    }

    /**
     * Retrieve collection of customer wishlists
     *
     * @return \Magento\Wishlist\Model\ResourceModel\Wishlist\Collection
     * @since 2.0.0
     */
    public function getCustomerWishlists()
    {
        return $this->_wishlistFactory->create()->getCollection()->filterByCustomerId($this->getCustomerId());
    }

    /**
     * Get the item unit price html
     *
     * @param Item $item
     * @return string
     * @since 2.0.0
     */
    public function getItemUnitPriceHtml(Item $item)
    {
        $block = $this->getLayout()->getBlock('item_unit_price');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Get the item row total html
     *
     * @param Item $item
     * @return string
     * @since 2.0.0
     */
    public function getItemRowTotalHtml(Item $item)
    {
        $block = $this->getLayout()->getBlock('item_row_total');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Return html for row total with discount
     *
     * @param Item $item
     * @return string
     * @since 2.0.0
     */
    public function getItemRowTotalWithDiscountHtml(Item $item)
    {
        $block = $this->getLayout()->getBlock('item_row_total_with_discount');
        $block->setItem($item);
        return $block->toHtml();
    }
}
