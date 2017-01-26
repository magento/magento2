<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Block\Cart;

/**
 * Block on checkout/cart/index page to display a pager on the  cart items grid
 * The pager will be displayed if items quantity in the shopping cart > than number from
 * Store->Configuration->Sales->Checkout->Shopping Cart->Number of items to display pager and
 * custom_items weren't set to cart block
 */
class Grid extends \Magento\Checkout\Block\Cart
{
    /**
     * Config settings path to determine when pager on checkout/cart/index will be visible
     */
    const XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER = 'checkout/cart/number_items_to_display_pager';

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\Collection
     */
    private $itemsCollection;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     *
     */
    private $itemCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $joinAttributeProcessor;

    /**
     * Grid constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        array $data = []
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->joinAttributeProcessor = $joinProcessor;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $data
        );
    }

    /**
     * Prepare Quote Item Product URLs
     * When we don't have custom_items, items URLs will be collected for Collection limited by pager
     * Pager limit on checkout/cart/index is determined by configuration
     * Configuration path is Store->Configuration->Sales->Checkout->Shopping Cart->Number of items to display pager
     *
     * @return void
     */
    protected function _construct()
    {
        if ($this->getCustomItems()) {
            parent::_construct();
        }
        if ($this->hasData('template')) {
            $this->setTemplate($this->getData('template'));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $availableLimit = $this->_scopeConfig->getValue(
            self::XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$this->getCustomItems()) {
            $itemsCollection = $this->getItemsForGrid();
            /** @var  \Magento\Theme\Block\Html\Pager $pager */
            $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class);
            $pager->setAvailableLimit([$availableLimit => $availableLimit])->setCollection($itemsCollection);
            $this->setChild('pager', $pager);
            $itemsCollection->load();
            $this->prepareItemUrls();
        }
        return $this;
    }

    /**
     * Prepare quote items collection for pager
     *
     * @return \Magento\Quote\Model\ResourceModel\Quote\Item\Collection
     */
    public function getItemsForGrid()
    {
        if (!$this->itemsCollection) {
            /** @var \Magento\Quote\Model\ResourceModel\Quote\Item\Collection $itemCollection */
            $itemCollection = $this->itemCollectionFactory->create();

            $itemCollection->setQuote($this->getQuote());
            $itemCollection->addFieldToFilter('parent_item_id', ['null' => true]);
            $this->joinAttributeProcessor->process($itemCollection);

            $this->itemsCollection = $itemCollection;
        }
        return $this->itemsCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if ($this->getCustomItems()) {
            return parent::getItems();
        }
        return $this->getItemsForGrid()->getItems();
    }
}
