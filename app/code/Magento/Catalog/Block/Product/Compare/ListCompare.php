<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\Compare;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Context;
use Magento\Framework\App\Action\Action;

/**
 * Catalog products compare block
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class ListCompare extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Product Compare items collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection
     * @since 2.0.0
     */
    protected $_items;

    /**
     * Compare Products comparable attributes cache
     *
     * @var array
     * @since 2.0.0
     */
    protected $_attributes;

    /**
     * Flag which allow/disallow to use link for as low as price
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_useLinkForAsLowAs = false;

    /**
     * Customer id
     *
     * @var null|int
     * @since 2.0.0
     */
    protected $_customerId = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 2.0.0
     */
    protected $httpContext;

    /**
     * Customer visitor
     *
     * @var \Magento\Customer\Model\Visitor
     * @since 2.0.0
     */
    protected $_customerVisitor;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     * @since 2.0.0
     */
    protected $_catalogProductVisibility;

    /**
     * Item collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory
     * @since 2.0.0
     */
    protected $_itemCollectionFactory;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     * @since 2.0.0
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     * @since 2.0.0
     */
    protected $currentCustomer;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory
     * @param Product\Visibility $catalogProductVisibility
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        $this->urlEncoder = $urlEncoder;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_customerVisitor = $customerVisitor;
        $this->httpContext = $httpContext;
        $this->currentCustomer = $currentCustomer;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Get add to wishlist params
     *
     * @param Product $product
     * @return string
     * @since 2.0.0
     */
    public function getAddToWishlistParams($product)
    {
        $continueUrl = $this->urlEncoder->encode($this->getUrl('customer/account'));
        $urlParamName = Action::PARAM_NAME_URL_ENCODED;

        $continueUrlParams = [$urlParamName => $continueUrl];

        return $this->_wishlistHelper->getAddParams($product, $continueUrlParams);
    }

    /**
     * Preparing layout
     *
     * @return \Magento\Catalog\Block\Product\Compare\ListCompare
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Products Comparison List') . ' - ' . $this->pageConfig->getTitle()->getDefault()
        );
        return parent::_prepareLayout();
    }

    /**
     * Retrieve Product Compare items collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection
     * @since 2.0.0
     */
    public function getItems()
    {
        if ($this->_items === null) {
            $this->_compareProduct->setAllowUsedFlat(false);

            $this->_items = $this->_itemCollectionFactory->create();
            $this->_items->useProductItem(true)->setStoreId($this->_storeManager->getStore()->getId());

            if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
                $this->_items->setCustomerId($this->currentCustomer->getCustomerId());
            } elseif ($this->_customerId) {
                $this->_items->setCustomerId($this->_customerId);
            } else {
                $this->_items->setVisitorId($this->_customerVisitor->getId());
            }

            $this->_items->addAttributeToSelect(
                $this->_catalogConfig->getProductAttributes()
            )->loadComparableAttributes()->addMinimalPrice()->addTaxPercents()->setVisibility(
                $this->_catalogProductVisibility->getVisibleInSiteIds()
            );
        }

        return $this->_items;
    }

    /**
     * Retrieve Product Compare Attributes
     *
     * @return array
     * @since 2.0.0
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = $this->getItems()->getComparableAttributes();
        }

        return $this->_attributes;
    }

    /**
     * Retrieve Product Attribute Value
     *
     * @param Product $product
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return \Magento\Framework\Phrase|string
     * @since 2.0.0
     */
    public function getProductAttributeValue($product, $attribute)
    {
        if (!$product->hasData($attribute->getAttributeCode())) {
            return __('N/A');
        }

        if ($attribute->getSourceModel() || in_array(
            $attribute->getFrontendInput(),
            ['select', 'boolean', 'multiselect']
        )
        ) {
            //$value = $attribute->getSource()->getOptionText($product->getData($attribute->getAttributeCode()));
            $value = $attribute->getFrontend()->getValue($product);
        } else {
            $value = $product->getData($attribute->getAttributeCode());
        }
        return (string)$value == '' ? __('No') : $value;
    }

    /**
     * Retrieve Print URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/*', ['_current' => true, 'print' => 1]);
    }

    /**
     * Setter for customer id
     *
     * @param int $id
     * @return \Magento\Catalog\Block\Product\Compare\ListCompare
     * @since 2.0.0
     */
    public function setCustomerId($id)
    {
        $this->_customerId = $id;
        return $this;
    }

    /**
     * Render price block
     *
     * @param Product $product
     * @param string|null $idSuffix
     * @return string
     * @since 2.0.0
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product, $idSuffix = '')
    {
        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'price_id' => 'product-price-' . $product->getId() . $idSuffix,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                ]
            );
        }
        return $price;
    }
}
