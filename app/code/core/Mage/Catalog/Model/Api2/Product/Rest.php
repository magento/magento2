<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract API2 class for product instance
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Catalog_Model_Api2_Product_Rest extends Mage_Catalog_Model_Api2_Product
{
    /**
     * Current loaded product
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * Retrieve product data
     *
     * @return array
     */
    protected function _retrieve()
    {
        $product = $this->_getProduct();

        $this->_prepareProductForResponse($product);
        return $product->getData();
    }

    /**
     * Retrieve list of products
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Collection');
        $store = $this->_getStore();
        $entityOnlyAttributes = $this->getEntityOnlyAttributes($this->getUserType(),
            Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ);
        $availableAttributes = array_keys($this->getAvailableAttributes($this->getUserType(),
            Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ));
        // available attributes not contain image attribute, but it needed for get image_url
        $availableAttributes[] = 'image';
        $collection->addStoreFilter($store->getId())
            ->addPriceData($this->_getCustomerGroupId(), $store->getWebsiteId())
            ->addAttributeToSelect(array_diff($availableAttributes, $entityOnlyAttributes))
            ->addAttributeToFilter('visibility', array(
                'neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
        $this->_applyCategoryFilter($collection);
        $this->_applyCollectionModifiers($collection);
        $products = $collection->load();

        /** @var Mage_Catalog_Model_Product $product */
        foreach ($products as $product) {
            $this->_setProduct($product);
            $this->_prepareProductForResponse($product);
        }
        return $products->toArray();
    }

    /**
     * Apply filter by category id
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */
    protected function _applyCategoryFilter(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        if ($categoryId) {
            $category = $this->_getCategoryById($categoryId);
            if (!$category->getId()) {
                $this->_critical('Category not found.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $collection->addCategoryFilter($category);
        }
    }

    /**
     * Add special fields to product get response
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _prepareProductForResponse(Mage_Catalog_Model_Product $product)
    {
        /** @var $productHelper Mage_Catalog_Helper_Product */
        $productHelper = Mage::helper('Mage_Catalog_Helper_Product');
        $productData = $product->getData();
        $product->setWebsiteId($this->_getStore()->getWebsiteId());
        // customer group is required in product for correct prices calculation
        $product->setCustomerGroupId($this->_getCustomerGroupId());
        // calculate prices
        $finalPrice = $product->getFinalPrice();
        $productData['regular_price_with_tax'] = $this->_applyTaxToPrice($product->getPrice(), true);
        $productData['regular_price_without_tax'] = $this->_applyTaxToPrice($product->getPrice(), false);
        $productData['final_price_with_tax'] = $this->_applyTaxToPrice($finalPrice, true);
        $productData['final_price_without_tax'] = $this->_applyTaxToPrice($finalPrice, false);

        $productData['is_saleable'] = $product->getIsSalable();
        $productData['image_url'] = (string) Mage::helper('Mage_Catalog_Helper_Image')->init($product, 'image');

        if ($this->getActionType() == self::ACTION_TYPE_ENTITY) {
            // define URLs
            $productData['url'] = $productHelper->getProductUrl($product->getId());
            /** @var $cartHelper Mage_Checkout_Helper_Cart */
            $cartHelper = Mage::helper('Mage_Checkout_Helper_Cart');
            $productData['buy_now_url'] = $cartHelper->getAddUrl($product);

            /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            $stockItem = $product->getStockItem();
            if (!$stockItem) {
                $stockItem = Mage::getModel('Mage_CatalogInventory_Model_Stock_Item');
                $stockItem->loadByProduct($product);
            }
            $productData['is_in_stock'] = $stockItem->getIsInStock();

            /** @var $reviewModel Mage_Review_Model_Review */
            $reviewModel = Mage::getModel('Mage_Review_Model_Review');
            $productData['total_reviews_count'] = $reviewModel->getTotalReviews($product->getId(), true,
                $this->_getStore()->getId());

            $productData['tier_price'] = $this->_getTierPrices();
            $productData['has_custom_options'] = count($product->getOptions()) > 0;
        } else {
            // remove tier price from response
            $product->unsetData('tier_price');
            unset($productData['tier_price']);
        }
        $product->addData($productData);
    }

    /**
     * Product create only available for admin
     *
     * @param array $data
     */
    protected function _create(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }

    /**
     * Product update only available for admin
     *
     * @param array $data
     */
    protected function _update(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }

    /**
     * Product delete only available for admin
     */
    protected function _delete()
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED);
    }

    /**
     * Load product by its SKU or ID provided in request
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        if (is_null($this->_product)) {
            $productId = $this->getRequest()->getParam('id');
            /** @var $productHelper Mage_Catalog_Helper_Product */
            $productHelper = Mage::helper('Mage_Catalog_Helper_Product');
            $product = $productHelper->getProduct($productId, $this->_getStore()->getId());
            if (!($product->getId())) {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }
            // check if product belongs to website current
            if ($this->getRequest()->getParam('store')) {
                $isValidWebsite = in_array($this->_getStore()->getWebsiteId(), $product->getWebsiteIds());
                if (!$isValidWebsite) {
                    $this->_critical(self::RESOURCE_NOT_FOUND);
                }
            }
            // Check display settings for customers & guests
            if ($this->getApiUser()->getType() != Mage_Api2_Model_Auth_User_Admin::USER_TYPE) {
                // check if product assigned to any website and can be shown
                if ((!Mage::app()->isSingleStoreMode() && !count($product->getWebsiteIds()))
                    || !$productHelper->canShow($product)) {
                    $this->_critical(self::RESOURCE_NOT_FOUND);
                }
            }
            $this->_product = $product;
        }
        return $this->_product;
    }

    /**
     * Set product
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->_product = $product;
    }

    /**
     * Load category by id
     *
     * @param int $categoryId
     * @return Mage_Catalog_Model_Category
     */
    protected function _getCategoryById($categoryId)
    {
        return Mage::getModel('Mage_Catalog_Model_Category')->load($categoryId);
    }

    /**
     * Get product price with all tax settings processing
     *
     * @param float $price inputed product price
     * @param bool $includingTax return price include tax flag
     * @param null|Mage_Customer_Model_Address $shippingAddress
     * @param null|Mage_Customer_Model_Address $billingAddress
     * @param null|int $ctc customer tax class
     * @param bool $priceIncludesTax flag that price parameter contain tax
     * @return float
     * @see Mage_Tax_Helper_Data::getPrice()
     */
    protected function _getPrice($price, $includingTax = null, $shippingAddress = null,
        $billingAddress = null, $ctc = null, $priceIncludesTax = null
    ) {
        $product = $this->_getProduct();
        $store = $this->_getStore();

        if (is_null($priceIncludesTax)) {
            /** @var $config Mage_Tax_Model_Config */
            $config = Mage::getSingleton('Mage_Tax_Model_Config');
            $priceIncludesTax = $config->priceIncludesTax($store) || $config->getNeedUseShippingExcludeTax();
        }

        $percent = $product->getTaxPercent();
        $includingPercent = null;

        $taxClassId = $product->getTaxClassId();
        if (is_null($percent)) {
            if ($taxClassId) {
                $request = Mage::getSingleton('Mage_Tax_Model_Calculation')
                    ->getRateRequest($shippingAddress, $billingAddress, $ctc, $store);
                $percent = Mage::getSingleton('Mage_Tax_Model_Calculation')->getRate($request->setProductClassId($taxClassId));
            }
        }
        if ($taxClassId && $priceIncludesTax) {
            $request = Mage::getSingleton('Mage_Tax_Model_Calculation')->getRateRequest(false, false, false, $store);
            $includingPercent = Mage::getSingleton('Mage_Tax_Model_Calculation')
                ->getRate($request->setProductClassId($taxClassId));
        }

        if ($percent === false || is_null($percent)) {
            if ($priceIncludesTax && !$includingPercent) {
                return $price;
            }
        }
        $product->setTaxPercent($percent);

        if (!is_null($includingTax)) {
            if ($priceIncludesTax) {
                if ($includingTax) {
                    /**
                     * Recalculate price include tax in case of different rates
                     */
                    if ($includingPercent != $percent) {
                        $price = $this->_calculatePrice($price, $includingPercent, false);
                        /**
                         * Using regular rounding. Ex:
                         * price incl tax   = 52.76
                         * store tax rate   = 19.6%
                         * customer tax rate= 19%
                         *
                         * price excl tax = 52.76 / 1.196 = 44.11371237 ~ 44.11
                         * tax = 44.11371237 * 0.19 = 8.381605351 ~ 8.38
                         * price incl tax = 52.49531773 ~ 52.50 != 52.49
                         *
                         * that why we need round prices excluding tax before applying tax
                         * this calculation is used for showing prices on catalog pages
                         */
                        if ($percent != 0) {
                            $price = Mage::getSingleton('Mage_Tax_Model_Calculation')->round($price);
                            $price = $this->_calculatePrice($price, $percent, true);
                        }
                    }
                } else {
                    $price = $this->_calculatePrice($price, $includingPercent, false);
                }
            } else {
                if ($includingTax) {
                    $price = $this->_calculatePrice($price, $percent, true);
                }
            }
        } else {
            if ($priceIncludesTax) {
                if ($includingTax) {
                    $price = $this->_calculatePrice($price, $includingPercent, false);
                    $price = $this->_calculatePrice($price, $percent, true);
                } else {
                    $price = $this->_calculatePrice($price, $includingPercent, false);
                }
            } else {
                if ($includingTax) {
                    $price = $this->_calculatePrice($price, $percent, true);
                }
            }
        }

        return $store->roundPrice($price);
    }

    /**
     * Calculate price imcluding/excluding tax base on tax rate percent
     *
     * @param float $price
     * @param float $percent
     * @param bool $includeTax true - for calculate price including tax and false if price excluding tax
     * @return float
     */
    protected function _calculatePrice($price, $percent, $includeTax)
    {
        /** @var $calculator Mage_Tax_Model_Calculation */
        $calculator = Mage::getSingleton('Mage_Tax_Model_Calculation');
        $taxAmount = $calculator->calcTaxAmount($price, $percent, !$includeTax, false);

        return $includeTax ? $price + $taxAmount : $price - $taxAmount;
    }

    /**
     * Retrive tier prices in special format
     *
     * @return array
     */
    protected function _getTierPrices()
    {
        $tierPrices = array();
        foreach ($this->_getProduct()->getTierPrice() as $tierPrice) {
            $tierPrices[] = array(
                'qty' => $tierPrice['price_qty'],
                'price_with_tax' => $this->_applyTaxToPrice($tierPrice['price']),
                'price_without_tax' => $this->_applyTaxToPrice($tierPrice['price'], false)
            );
        }
        return $tierPrices;
    }

    /**
     * Default implementation. May be different for customer/guest/admin role.
     *
     * @return null
     */
    protected function _getCustomerGroupId()
    {
        return null;
    }

    /**
     * Default implementation. May be different for customer/guest/admin role.
     *
     * @param float $price
     * @param bool $withTax
     * @return float
     */
    protected function _applyTaxToPrice($price, $withTax = true)
    {
        return $price;
    }
}
