<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Block\Cart;

/**
 * Block on checkout/cart/index page to display a pager on the cart items grid
 * The pager will not be displayed if custom_items are set to cart block
 *
 * @api
 * @since 100.2.0
 */
class Grid extends \Magento\Checkout\Block\Cart
{
    /**
     * Config settings path to determine the page size on the shopping cart page
     */
    const XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER = 'checkout/cart/number_items_to_display_pager';

    /**
     * Config settings path to determine the page size limiter on the shopping cart page
     */
    const XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER_VALUES = 'checkout/cart/number_items_to_display_pager_values';

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
     * Is display pager on shopping cart page
     *
     * @var bool
     */
    private $isPagerDisplayed;

    /**
     * The limit to apply for the Pager
     *
     * @var int
     */
    private $limit;

    /**
     * Limit variable name in the request parameters
     *
     * @var string
     */
    private $limitVarName = 'limit';

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
     * @since 100.2.0
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
     * Configuration path is Store->Configuration->Sales->Checkout->Shopping Cart->Items per Page Default Value
     *
     * @return void
     * @since 100.2.0
     */
    protected function _construct()
    {
        if (!$this->isPagerDisplayedOnPage()) {
            parent::_construct();
        }
        if ($this->hasData('template')) {
            $this->setTemplate($this->getData('template'));
        }
    }

    /**
     * Preparing global layout
     *
     * @since 100.2.0
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->isPagerDisplayedOnPage()) {
            $defaultLimit = $this->getLimit();
            $availableLimit = $this->getAvailableLimit();
            $itemsCollection = $this->getItemsForGrid();

            /** @var  \Magento\Theme\Block\Html\Pager $pager */
            $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class);
            $pager->setLimitVarName($this->limitVarName);
            $pager->setLimit($defaultLimit);
            $pager->setAvailableLimit($availableLimit);
            $pager->setCollection($itemsCollection);

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
     * @since 100.2.0
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
     * Return customer quote items
     *
     * @since 100.2.0
     */
    public function getItems()
    {
        if (!$this->isPagerDisplayedOnPage()) {
            return parent::getItems();
        }
        return $this->getItemsForGrid()->getItems();
    }

    /**
     * Verify if display pager on shopping cart
     *
     * If cart block has custom_items and items qty in the shopping cart<limit from stores configuration
     *
     * @return bool
     */
    private function isPagerDisplayedOnPage()
    {
        if (!$this->isPagerDisplayed) {
            $this->isPagerDisplayed = !$this->getCustomItems();
        }
        return $this->isPagerDisplayed;
    }

    /**
     * Get the page limit to apply
     *
     * @return int
     */
    private function getLimit(): int
    {
        if (!$this->limit) {
            $appliedLimit = $this->getRequest()->getParam($this->limitVarName, null);

            if ($appliedLimit) {
                $this->limit =  (int)$appliedLimit;

                return $this->limit;
            }

            $this->limit = (int)$this->_scopeConfig->getValue(
                self::XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $this->limit;
    }

    /**
     * Get the limiter options available for the customers
     *
     * @return array
     */
    private function getAvailableLimit(): array
    {
        $availableLimit = $this->_scopeConfig->getValue(
            self::XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER_VALUES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$availableLimit) {
            return [
                $this->getLimit() => $this->getLimit()
            ];
        }

        $availableLimit = explode(',', $availableLimit);
        $availableLimit = array_combine($availableLimit, $availableLimit);

        return $availableLimit;
    }
}
