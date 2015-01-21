<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Resource\Product\Compare\Item\Collection;

/**
 * Catalog Product Compare Helper
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Compare extends \Magento\Core\Helper\Url
{
    /**
     * Product Compare Items Collection
     *
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * Product Comapare Items Collection has items flag
     *
     * @var bool
     */
    protected $_hasItems;

    /**
     * Allow used Flat catalog product for product compare items collection
     *
     * @var bool
     */
    protected $_allowUsedFlat = true;

    /**
     * Customer id
     *
     * @var null|int
     */
    protected $_customerId = null;

    /**
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Customer visitor
     *
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Product compare item collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistHelper;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Core\Helper\PostData $coreHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Core\Helper\PostData $coreHelper
    ) {
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_customerVisitor = $customerVisitor;
        $this->_customerSession = $customerSession;
        $this->_catalogSession = $catalogSession;
        $this->_formKey = $formKey;
        $this->_wishlistHelper = $wishlistHelper;
        $this->_coreHelper = $coreHelper;
        parent::__construct($context, $storeManager);
    }

    /**
     * Retrieve compare list url
     *
     * @return string
     */
    public function getListUrl()
    {
        $itemIds = [];
        foreach ($this->getItemCollection() as $item) {
            $itemIds[] = $item->getId();
        }

        $params = [
            'items' => implode(',', $itemIds),
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
        ];

        return $this->_getUrl('catalog/product_compare', $params);
    }

    /**
     * Get parameters used for build add product to compare list urls
     *
     * @param Product $product
     * @return string
     */
    public function getPostDataParams($product)
    {
        return $this->_coreHelper->getPostData($this->getAddUrl(), ['product' => $product->getId()]);
    }

    /**
     * Retrieve url for adding product to compare list
     *
     * @return string
     */
    public function getAddUrl()
    {
        return $this->_getUrl('catalog/product_compare/add');
    }

    /**
     * Retrieve add to wishlist params
     *
     * @param Product $product
     * @return string
     */
    public function getAddToWishlistParams($product)
    {
        $beforeCompareUrl = $this->_catalogSession->getBeforeCompareUrl();

        $encodedUrl = [
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl($beforeCompareUrl)
        ];

        return $this->_wishlistHelper->getAddParams($product, $encodedUrl);
    }

    /**
     * Retrieve add to cart url
     *
     * @param Product $product
     * @return string
     */
    public function getAddToCartUrl($product)
    {
        $beforeCompareUrl = $this->_catalogSession->getBeforeCompareUrl();
        $params = [
            'product' => $product->getId(),
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl($beforeCompareUrl)
        ];

        return $this->_getUrl('checkout/cart/add', $params);
    }

    /**
     * Retrieve remove item from compare list url
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->_getUrl('catalog/product_compare/remove');
    }

    /**
     * Get parameters to remove products from compare list
     *
     * @param Product $product
     * @return string
     */
    public function getPostDataRemove($product)
    {
        $listCleanUrl = $this->getEncodedUrl($this->_getUrl('catalog/product_compare'));
        $data = [
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $listCleanUrl,
            'product' => $product->getId()
        ];
        return $this->_coreHelper->getPostData($this->getRemoveUrl(), $data);
    }

    /**
     * Retrieve clear compare list url
     *
     * @return string
     */
    public function getClearListUrl()
    {
        return $this->_getUrl('catalog/product_compare/clear');
    }

    /**
     * Get parameters to clear compare list
     *
     * @return string
     */
    public function getPostDataClearList()
    {
        $refererUrl = $this->_getRequest()->getServer('HTTP_REFERER');
        $params = [
            \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($refererUrl)
        ];
        return $this->_coreHelper->getPostData($this->getClearListUrl(), $params);
    }

    /**
     * Retrieve compare list items collection
     *
     * @return Collection
     */
    public function getItemCollection()
    {
        if (!$this->_itemCollection) {
            // cannot be placed in constructor because of the cyclic dependency which cannot be fixed with proxy class
            // collection uses this helper in constructor when calling isEnabledFlat() method
            $this->_itemCollection = $this->_itemCollectionFactory->create();
            $this->_itemCollection->useProductItem(true)->setStoreId($this->_storeManager->getStore()->getId());

            if ($this->_customerSession->isLoggedIn()) {
                $this->_itemCollection->setCustomerId($this->_customerSession->getCustomerId());
            } elseif ($this->_customerId) {
                $this->_itemCollection->setCustomerId($this->_customerId);
            } else {
                $this->_itemCollection->setVisitorId($this->_customerVisitor->getId());
            }

            $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInSiteIds());

            /* Price data is added to consider item stock status using price index */
            $this->_itemCollection->addPriceData();

            $this->_itemCollection->addAttributeToSelect('name')->addUrlRewrite()->load();

            /* update compare items count */
            $this->_catalogSession->setCatalogCompareItemsCount(count($this->_itemCollection));
        }

        return $this->_itemCollection;
    }

    /**
     * Calculate cache product compare collection
     *
     * @param bool $logout
     * @return $this
     */
    public function calculate($logout = false)
    {
        /** @var $collection Collection */
        $collection = $this->_itemCollectionFactory->create()
            ->useProductItem(true);
        if (!$logout && $this->_customerSession->isLoggedIn()) {
            $collection->setCustomerId($this->_customerSession->getCustomerId());
        } elseif ($this->_customerId) {
            $collection->setCustomerId($this->_customerId);
        } else {
            $collection->setVisitorId($this->_customerVisitor->getId());
        }

        /* Price data is added to consider item stock status using price index */
        $collection->addPriceData()
            ->setVisibility($this->_catalogProductVisibility->getVisibleInSiteIds());

        $count = $collection->getSize();
        $this->_catalogSession->setCatalogCompareItemsCount($count);

        return $this;
    }

    /**
     * Retrieve count of items in compare list
     *
     * @return int
     */
    public function getItemCount()
    {
        if (!$this->_catalogSession->hasCatalogCompareItemsCount()) {
            $this->calculate();
        }

        return $this->_catalogSession->getCatalogCompareItemsCount();
    }

    /**
     * Check has items
     *
     * @return bool
     */
    public function hasItems()
    {
        return $this->getItemCount() > 0;
    }

    /**
     * Set is allow used flat (for collection)
     *
     * @param bool $flag
     * @return $this
     */
    public function setAllowUsedFlat($flag)
    {
        $this->_allowUsedFlat = (bool)$flag;
        return $this;
    }

    /**
     * Retrieve is allow used flat (for collection)
     *
     * @return bool
     */
    public function getAllowUsedFlat()
    {
        return $this->_allowUsedFlat;
    }

    /**
     * Setter for customer id
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerId($id)
    {
        $this->_customerId = $id;
        return $this;
    }
}
