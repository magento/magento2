<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\ProductList;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Checkout\Model\ResourceModel\Cart as CartResourceModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Catalog product related items block
 *
 * @api
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 100.0.2
 */
class Related extends AbstractProduct implements IdentityInterface
{
    /**
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * Checkout session
     *
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * Catalog product visibility
     *
     * @var ProductVisibility
     */
    protected $_catalogProductVisibility;

    /**
     * Checkout cart
     *
     * @var CartResourceModel
     */
    protected $_checkoutCart;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @param Context $context
     * @param CartResourceModel $checkoutCart
     * @param ProductVisibility $catalogProductVisibility
     * @param CheckoutSession $checkoutSession
     * @param Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        CartResourceModel $checkoutCart,
        ProductVisibility $catalogProductVisibility,
        CheckoutSession $checkoutSession,
        Manager $moduleManager,
        array $data = []
    ) {
        $this->_checkoutCart = $checkoutCart;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_checkoutSession = $checkoutSession;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $data);
    }

    /**
     * Prepare data
     *
     * @return $this
     */
    protected function _prepareData()
    {
        $product = $this->getProduct();
        /* @var $product Product */

        $this->_itemCollection = $product->getRelatedProductCollection()->addAttributeToSelect(
            'required_options'
        )->setPositionOrder()->addStoreFilter();

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * Before to html handler
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * Get collection items
     *
     * @return Collection
     */
    public function getItems()
    {
        /**
         * getIdentities() depends on _itemCollection populated, but it can be empty if the block is hidden
         * @see https://github.com/magento/magento2/issues/5897
         */
        if ($this->_itemCollection === null) {
            $this->_prepareData();
        }
        return $this->_itemCollection;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [[]];
        foreach ($this->getItems() as $item) {
            $identities[] = $item->getIdentities();
        }
        return array_merge(...$identities);
    }

    /**
     * Find out if some products can be easy added to cart
     *
     * @return bool
     */
    public function canItemsAddToCart()
    {
        foreach ($this->getItems() as $item) {
            if (!$item->isComposite() && $item->isSaleable() && !$item->getRequiredOptions()) {
                return true;
            }
        }
        return false;
    }
}
