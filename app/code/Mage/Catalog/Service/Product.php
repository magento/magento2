<?php
/**
 * API Product service.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @Service product
 * @Version 1.0
 * @Path /products
 */
class Mage_Catalog_Service_Product
{

    /** @var Mage_Catalog_Model_Product */
    protected $_product;

    /** @var Mage_Catalog_Helper_Data */
    protected $_helperData;

    /** @var Mage_Catalog_Helper_Image */
    protected $_helperImage;

    /** @var \Magento_ObjectManager */
    protected $_objectManager;

    /** @var \Mage_Core_Model_Design_Package */
    protected $_designPackage;

    /** @var \Mage_Core_Model_UrlInterface */
    protected $_urlBuilder;

    /** @var \Mage_Core_Model_StoreManager */
    protected $_storeManager;

    /** @var \Mage_Catalog_Model_Session */
    protected $_session;

    /** @var \Mage_Tax_Helper_Data */
    protected $_taxHelperData;

    /** @var \Mage_Core_Helper_Data */
    protected $_coreHelperData;

    protected $_productHelper;

    /**
     * @param Mage_Catalog_Helper_Data $helperData
     * @param Mage_Core_Helper_Data $coreHelperData
     * @param Mage_Tax_Helper_Data $taxHelperData
     * @param Mage_Catalog_Helper_Image $helperImage
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Model_UrlInterface $urlBuilder
     * @param Mage_Core_Model_StoreManager $storeManager
     * @param Mage_Catalog_Model_Session $session
     * @param Mage_Catalog_Helper_Product $productHelper
     */
    public function __construct(Mage_Catalog_Helper_Data $helperData,
                                Mage_Core_Helper_Data $coreHelperData,
                                Mage_Tax_Helper_Data $taxHelperData,
                                Mage_Catalog_Helper_Image $helperImage,
                                Magento_ObjectManager $objectManager,
                                Mage_Core_Model_Design_Package $designPackage,
                                Mage_Core_Model_UrlInterface $urlBuilder,
                                Mage_Core_Model_StoreManager $storeManager,
                                Mage_Catalog_Model_Session $session,
                                Mage_Catalog_Helper_Product $productHelper)
    {

        $this->_helperData = $helperData;
        $this->_helperImage = $helperImage;
        $this->_objectManager = $objectManager;
        $this->_designPackage = $designPackage;
        $this->_urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        $this->_taxHelperData = $taxHelperData;
        $this->_coreHelperData = $coreHelperData;
        $this->_productHelper = $productHelper;
    }



    /**
     * Returns info about one particular product.
     *
     * @Type call
     * @Method GET
     * @Path /:id
     * @Bindings [REST]
     * @Consumes (schema="etc/resources/product/item/input.xsd", element="id")
     * @Produces (schema="etc/resources/product/item/output.xsd", element="product")
     * @param int $id
     * @return array
     */
    public function item($id)
    {
        $this->_getObject($id);
        $data = $this->getProduct($id);
        $data = array_merge($data, $this->getAdditionalDetails($id));
        return $data;
    }


    /**
     * Returns info about several products.
     *
     * @Type call
     * @Method GET
     * @Bindings [REST]
     * @Consumes (schema="etc/resources/product/item/input.xsd", element="id")
     * @Produces (schema="etc/resources/product/item/output.xsd", element="products")
     *
     * @return array
     */
    public function items()
    {
        //@todo not implemented
        return array();
    }

    /**
     * Returns info about one particular product.
     *
     * @Type call
     * @Method GET
     * @Path /:id
     * @Bindings [REST]
     * @Consumes (schema="etc/resources/product/item/input.xsd", element="id")
     * @Produces (schema="etc/resources/product/item/output.xsd", element="related_products")
     * @param int $productId
     * @return array
     */
    public function getRelatedProducts($productId)
    {
        $this->_product = $this->_getObject($productId);

        $itemCollection = $this->_product->getRelatedProductCollection()
            ->addAttributeToSelect('required_options')
            ->setPositionOrder()
            ->addStoreFilter();

        if ($this->_helperData->isModuleEnabled('Mage_Checkout')) {
            Mage::getResourceSingleton('Mage_Checkout_Model_Resource_Cart')
                ->addExcludeProductFilter(
                    $itemCollection,
                    Mage::getSingleton('Mage_Checkout_Model_Session')->getQuoteId()
                );
            $itemCollection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect(Mage::getSingleton('Mage_Catalog_Model_Config')->getProductAttributes())
                ->addUrlRewrite();
        }
        $itemCollection->setVisibility(
            $this->_objectManager->get('Mage_Catalog_Model_Product_Visibility')
                ->getVisibleInCatalogIds()
        );

        $thumbnailImageSize = $this->_designPackage->getViewConfig()
            ->getVarValue('Mage_Catalog', 'product_thumbnail_image_sidebar_size');

        $items = array();
        /** @var $product Mage_Catalog_Model_Product */
        foreach ($itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
            $items[] = array(
                'thumbnailUrl' => (string)$this->_helperImage->init($product, 'thumbnail')
                    ->resize($thumbnailImageSize),
                'thumbnailSize' => $thumbnailImageSize,
                'composite' => $product->isComposite(),
                'saleable' => $product->isSaleable(),
                'hasRequiredOptions' => $product->getRequiredOptions(),
                'id' => $product->getId(),
                'productUrl' => $product->getProductUrl(),
                'name' => $product->getName(),
                'product' => $product
            );
        }

        $dictionary = array(
            'items' => $items,
        );
        return $dictionary;
    }


    /**
     * Format product data structure
     *
     * @param $productId
     * @return array
     */
    public function getProduct($productId)
    {
        $product = $this->_getObject($productId);
        $result = array(
            'sku' => $product->getSku(),
            'name' => $this->_product->getName(),
            'description' => $this->_product->getDescription(),
            'shortDescription' => $this->_product->getShortDescription(),
            'price' => $this->_getPriceValue($this->_product->getPrice()),
            'weight' => $this->_product->getWeight(),
            'status' => $this->_product->getStatus(),
            'visibility' => $this->_product->getVisibility(),
            'createdAt' => $this->_product->getCreatedAt(),
            'updatedAt' => $this->_product->getUpdatedAt(),
            'taxClassId' => $this->_product->getTaxClassId(),
            'entityId' => $product->getId(),
            'productType' => $product->getTypeId(),
            'isSaleable' => $product->isSaleable(),
            'isGrouped' => $product->isGrouped(),
            'options' => $this->_getOptions($product),
            'storeId' => $product->getStoreId(),
            'attributesData' => $this->_getAttributesData($product),
            'defaultQuantity' => $this->_getProductDefaultQty($product),
        );

        if ($specialPrice = $this->_getSpecialPrice($this->_product)) {
            $result['specialPrice'] = $specialPrice;
        }
        if ($this->_product->getCost()) {
            $result['cost'] = $this->_getPriceValue($this->_product->getCost());
        }

        if ($images = $this->_getImages($this->_product)) {
            $result['images'] = $images;
        }

        if ($websiteIds = $this->_getWebsiteIds($this->_product)) {
            $result['websiteIds'] = $websiteIds;
        }

        if ($mediaGallery = $this->_getMediaGallery($this->_product)) {
            $result['mediaGallery'] = $mediaGallery;
        }

        if ($this->_product->getGroupPrice()) {
            $result['groupPrice'] = $this->_getPriceValue($this->_product->getGroupPrice());
        }
        if ($this->_product->getTierPrice()) {
            $result['tierPrice'] = $this->_product->getTierPrice();
        }

        if ($this->_product->getMinimalPrice()) {
            $result['minimalPrice'] = $this->_getPriceValue($this->_product->getMinimalPrice());
        }
        if ($this->_product->getMsrp()) {
            $result['msrp'] = $this->_getPriceValue($this->_product->getMsrp());
        }
        if ($downloadable = $this->_getDownloadableField($this->_product)) {
            $result['downloadable'] = $downloadable;
        }

        if ($qtyAndStock = $this->_getQuantityAndStockStatus($this->_product)) {
            $result = array_merge($result, $qtyAndStock);
        }
        if ($bundle = $this->_getBundle($this->_product)) {
            $result['bundle'] = $bundle;
        }
        $result['giftcard'] = $this->_getGitCard($this->_product);

        $result['hasOptions'] = $this->_product->getTypeInstance()->hasOptions($this->_product);

        $schema = array(
            'manufacturer',
            'metaTitle',
            'metaKeyword',
            'metaDescription',
            'oldId',
            'color',
            'newsFromDate',
            'newsToDate',
            'gallery',
            'urlKey',
            'urlPath',
            'isRecurring',
            'recurringProfile',
            'customDesign',
            'customDesignFrom',
            'customDesignTo',
            'customLayoutUpdate',
            'pageLayout',
            'categoryIds',
            'optionsContainer',
            'requiredOptions',
            'countryOfManufacture',
            'msrpEnabled',
            'msrpDisplayActualPriceType',
            'giftMessageAvailable',
            'enableGoogleCheckout',
            'giftcard',
            'isReturnable',
        );

        $schemaValues = $this->_getDataBySchema($this->_product, $schema);
        $result = array_merge($result, $schemaValues);
        return $result;
    }

    /**
     * Retrieve data from product by field names
     *
     * @param $product
     * @param $schema
     * @return array
     */
    public function _getDataBySchema($product, $schema)
    {
        $result = array();
        foreach ($schema as $field) {
            $name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $field));
            if ($product->hasData($name)) {
                $result[$field] = $product->getData($name);
            }
        }
        return $result;
    }

    /**
     * Product additional data
     *
     * @param $productId
     * @return array
     */
    public function getAdditionalDetails($productId)
    {
        $product = $this->_getObject($productId);
        $sendToFriendModel = Mage::registry('send_to_friend_model');
        $dictionary = array(
            'categoryId' => Mage::getSingleton('Mage_Catalog_Model_Session')->getLastVisitedCategoryId(),
            'canEmailToFriend' => $sendToFriendModel && $sendToFriendModel->canEmailToFriend(),
            'jsonConfig' => $this->getJsonConfig(),
        );
        return $dictionary;
    }

    protected function _getWebsiteIds($product)
    {
        return null;
    }

    /**
     * Format price value
     *
     * @param $price
     * @return array
     */
    protected function _getPriceValue($price)
    {
        $currCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        return array(
            'amount' => $price,
            'currencyCode' => $currCode,
            'formattedPrice' => $this->_coreHelperData->currency($price, true, false),
        );
    }

    /**
     * Format special price
     *
     * @param $product
     * @return array|null
     */
    protected function _getSpecialPrice($product)
    {
        if ($product->getSpecialPrice()) {
            return array(
                'specialPrice' => $product->getSpecialPrice(),
                'specialFromDate' => $product->getSpecialFromDate(),
                'specialToDate' => $product->getSpecialToDate(),
            );
        }
        return null;
    }

    /**
     * Format images
     *
     * @param $product
     * @return array|null
     */
    protected function _getImages($product)
    {
        $thumbnailSize = $this->_designPackage->getViewConfig()
            ->getVarValue('Mage_Catalog', 'product_thumbnail_image_size');
        $smallImageSize = $this->_designPackage->getViewConfig()
            ->getVarValue('Mage_Catalog', 'product_small_image_size');

        $images = array();
        $image = $product->getImage();
        $smallImage = $product->getSmallImage();
        $thumbnail = $product->getThumbnail();
        if ($image) {
            $images['image'] = (string)$this->_helperImage->init($product, 'image');
        }
        if ($smallImage) {
            $images['smallImage'] = (string)$this->_helperImage->init($product, 'small_image')
                ->resize($smallImageSize);
        }
        if ($thumbnail) {
            $images['thumbnail'] = (string)$this->_helperImage->init($product, 'thumbnail')
                ->resize($thumbnailSize);
        }
        $label = $this->_product->getData('image_label');
        if (empty($label)) {
            $label = $this->_product->getName();
        }

        if (!empty($images)) {
            $images['label'] = $label;
            return $images;
        }
        return null;
    }

    /**
     * Format media gallery
     *
     * @param $product
     * @return array|null
     */
    protected function _getMediaGallery($product)
    {
        $images = array();
        $iconSize = $this->_designPackage->getViewConfig()
            ->getVarValue('Mage_Catalog', 'product_base_image_icon_size');
        foreach ($product->getMediaGalleryImages() as $image) {
            $images[] = array(
                'image' => array(
                    'valueId' => (int)$image->getValueId(),
                    'file' => $image->getFile(),
                    'label' => $image->getLabel(),
                    'position' => (int)$image->getPosition(),
                    'isDisabled' => false,
                    'labelDefault' => $image->getLabelDefault(),
                    'positionDefault' => (int)$image->getPositionDefault(),
                    'isDisabledDefault' => (boolean)$image->getDisabledDefault(),
                    'url' => (string)$this->_helperImage->init($product, 'image', $image->getFile())
                        ->resize($iconSize),
                    'id' => $image->getId(),
                    'path' => $image->getPath(),
                )
            );
        }

        if (!empty($images)) {
            return array(
                'images' => $images
            );
        }
        return null;
    }

    /**
     * format stock and quantity
     *
     * @param $product
     * @return array|null
     */
    protected function _getQuantityAndStockStatus($product)
    {
        $quantityAndStockStatus = $product->getQuantityAndStockStatus();
        if (!is_null($quantityAndStockStatus['is_in_stock']) || !is_null($quantityAndStockStatus['qty'])) {
            return array(
                'isInStock' => $quantityAndStockStatus['is_in_stock'],
                'qty' => $quantityAndStockStatus['qty']
            );
        }
        return null;
    }

    /**
     * format bundle
     *
     * @param $product
     * @return array|null
     */
    protected function _getBundle($product)
    {
        if($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            return array(
                'priceType' => $product->getPriceType(),
                'skuType' => $product->getSkuType(),
                'weightType' => $product->getWeightType(),
                'priceView' => $product->getPriceView(),
                'shipmentType' => $product->getShipmentType(),
            );
        }
        return null;
    }

    /**
     * format downloadable product
     * @param $product
     * @return array|null
     */
    protected function _getDownloadableField($product)
    {
        if($product->getTypeId() === Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            return array(
                'linksPurchasedSeparately' => $product->getLinksPurchasedSeparately(),
                'samplesTitle' => $product->getSamplesTitle(),
                'linksTitle' => $product->getLinksTitle(),
                'shipmentType' => $product->getShipmentType(),
            );
        }
        return null;
    }

    /**
     * format Giftcards for the product
     *
     * @param $product
     * @return array
     */
    protected function _getGitCard($product)
    {
        return array(
            'giftcardAmounts' => array(
                array(
                    'amount' => array(
                        'amount' => '',
                        'currencyCode' => '',
                        'formattedPrice' => '',
                    )
                )
            ),
            'allowOpenAmount' => $product->getAllowOpenAmount(),
            'openAmountMin' => $this->_getPriceValue($product->getOpenAmountMin()),
            'openAmountMax' => $this->_getPriceValue($product->getOpenAmountMax()),
            'giftcardType' => $product->getGiftCardType(),
            'isRedeemable' => $product->getIsRedeemable(),
            'useConfigIsRedeemable' => $product->getUseConfigIsRedeemable(),
            'lifetime' => $product->getLifetime(),
            'useConfigLifetime' => $product->getUseConfigLifetime(),
            'emailTemplate' => $product->getEmailTemplate(),
            'useConfigEmailTemplate' => $product->getUseConfigEmailTemplate(),
            'allowMessage' => $product->getAllowMessage(),
            'useConfigAllowMessage' => $product->getUseConfigAllowMessage(),
        );
    }

    /**
     * Product Attributes data
     * @param $product
     * @return array
     */
    protected function _getAttributesData($product)
    {
        $data = array();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                $value = $attribute->getFrontend()->getValue($product);

                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = $this->_helperData->__('N/A');
                } elseif ((string)$value == '') {
                    $value = $this->_helperData->__('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->_storeManager->getStore()->convertPrice($value, true);
                }

                if (is_string($value) && strlen($value)) {
                    $data[$attribute->getAttributeCode()] = array(
                        'label' => $attribute->getStoreLabel(),
                        'output' => $value
                    );
                }
            }
        }
        return $data;
    }

    /**
     * Returns model which operated by current service.
     *
     * @param mixed $productIdOrSku
     * @param string $fieldSetId
     * @return Mage_Catalog_Model_Product|Varien_Object
     * @throws Mage_Core_Service_Entity_Exception
     */
    protected function _getObject($productIdOrSku, $fieldSetId = '')
    {
        $this->_product = $this->_productHelper->getProduct($productIdOrSku, null);

        if (!$this->_product->getId()) {
            throw new Mage_Core_Service_Entity_Exception;
        }

        $this->_product->load($this->_product->getId());

        if ($this->_product->getId()) {
            $isVisible = $this->_product->isVisibleInCatalog() && $this->_product->isVisibleInSiteVisibility();
            $withinWebsite = in_array($this->_storeManager->getStore()->getWebsiteId(), $this->_product->getWebsiteIds());

            if (!$isVisible || !$withinWebsite) {
                throw new Mage_Core_Service_Entity_Exception;
            }
        } else {
            throw new Mage_Core_Service_Entity_Exception;
        }

        return $this->_product;
    }





    /**
     * Get JSON encoded configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $config = array();
        if (!$this->_product->getTypeInstance()->hasOptions($this->_product)) {
            return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($config);
        }

        $_request = Mage::getSingleton('Mage_Tax_Model_Calculation')->getRateRequest(false, false, false);
        /* @var $product Mage_Catalog_Model_Product */
        $product = $this->_product;
        $_request->setProductClassId($product->getTaxClassId());
        $defaultTax = Mage::getSingleton('Mage_Tax_Model_Calculation')->getRate($_request);

        $_request = Mage::getSingleton('Mage_Tax_Model_Calculation')->getRateRequest();
        $_request->setProductClassId($product->getTaxClassId());
        $currentTax = Mage::getSingleton('Mage_Tax_Model_Calculation')->getRate($_request);

        $_regularPrice = $product->getPrice();
        $_finalPrice = $product->getFinalPrice();
        $_priceInclTax = Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, $_finalPrice, true);
        $_priceExclTax = Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, $_finalPrice);
        $_tierPrices = array();
        $_tierPricesInclTax = array();
        foreach ($product->getTierPrice() as $tierPrice) {
            $_tierPrices[] = Mage::helper('Mage_Core_Helper_Data')->currency($tierPrice['website_price'], false, false);
            $_tierPricesInclTax[] = Mage::helper('Mage_Core_Helper_Data')->currency(
                Mage::helper('Mage_Tax_Helper_Data')->getPrice($product, (int)$tierPrice['website_price'], true),
                false, false);
        }
        $config = array(
            'productId'           => $product->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('Mage_Tax_Helper_Data')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('Mage_Tax_Helper_Data')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('Mage_Tax_Helper_Data')->displayBothPrices(),
            'productPrice'        => Mage::helper('Mage_Core_Helper_Data')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('Mage_Core_Helper_Data')->currency($_regularPrice, false, false),
            'priceInclTax'        => Mage::helper('Mage_Core_Helper_Data')->currency($_priceInclTax, false, false),
            'priceExclTax'        => Mage::helper('Mage_Core_Helper_Data')->currency($_priceExclTax, false, false),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'plusDispositionTax'  => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
            'tierPrices'          => $_tierPrices,
            'tierPricesInclTax'   => $_tierPricesInclTax,
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object'=>$responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option=>$value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($config);
    }

    /**
     * Get default qty - either as pre-configured, or as 1.
     * Also restricts it by minimal qty.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int|float
     */
    protected function _getProductDefaultQty($product)
    {
        $qty = $this->_getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }
        return $qty;
    }

    /**
     * Gets minimal sales quantity
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int|null
     */
    protected function _getMinimalQty($product)
    {
        $stockItem = $this->_product->getStockItem();
        if ($stockItem) {
            return ($stockItem->getMinSaleQty()
                && $stockItem->getMinSaleQty() > 0 ? $stockItem->getMinSaleQty() * 1 : null);
        }
        return null;
    }

    /**
     * @param $optionId
     * @return mixed|Varien_Object
     */
    protected function _getFileInfo($optionId)
    {
        $info = $this->_product->getPreconfiguredValues()->getData('options/' . $optionId);
        if (empty($info)) {
            $info = new Varien_Object();
        } else if (is_array($info)) {
            $info = new Varien_Object($info);
        }
        return $info;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function _getOptions($product)
    {
        $optionModels = $product->getOptions();
        $optionList = array();

        /** @var $option Mage_Catalog_Model_Product_Option */
        foreach ($optionModels as $key => $option) {
            $optionList[$key] = array(
                'id' => $option->getId(),
                'type' => $option->getType(),
                'groupByType' => $option->getGroupByType(),
                'isRequired' => $option->getIsRequire(),
                'title' => $option->getTitle(),
                'formattedPrice' => $option->getFormatedPrice(),
                'decoratedIsLast' => $option->getDecoratedIsLast(),
                'maxCharacters' => $option->getMaxCharacters(),
                'defaultValue' => $product->getPreconfiguredValues()->getData('options/' . $option->getId())
            );

            if ($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_FILE) {
                $fileFields = array(
                    // File info
                    'fileInfo' => $this->_getFileInfo($option->getId()),
                    'fileExtension'=> $option->getFileExtension(),
                    'imageSizeX' => $option->getImageSizeX(),
                    'imageSizeY' => $option->getImageSizeY()
                );
                $optionList[$key] = array_merge($optionList[$key], $fileFields);
            } elseif (($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE) ||
                ($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE_TIME) ||
                ($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_TIME))
            {
                $optionTypeDate = $this->_objectManager->get('Mage_Catalog_Model_Product_Option_Type_Date');
                $datetimeFields = array(
                    // Calendar info
                    'dateFieldOrder' => $optionTypeDate->getConfigData('date_fields_order'),
                    'dateOptionsById' => $product->getPreconfiguredValues()->getData('options/' . $option->getId() . '/date'),
                    'is24hTimeFormat' => $optionTypeDate->is24hTimeFormat(),
                    'nameOptionsById' => $product->getPreconfiguredValues()->getData('options/' . $option->getId() . '/name'),
                    'useCalendar' => $optionTypeDate->useCalendar(),
                    'yearEnd' => $optionTypeDate->getYearEnd(),
                    'yearStart' => $optionTypeDate->getYearStart(),
                );
                
                $optionList[$key] = array_merge($optionList[$key], $datetimeFields);
            }
        }

        $options = array(
            'optionList' => $optionList,
            'jsonConfig' => $this->_getJsonConfig($this->_product)
        );


        return $options;
    }

    /**
     * Get json representation of
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    protected function _getJsonConfig($product)
    {
        $config = array();

        foreach ($product->getOptions() as $option) {
            /* @var $option Mage_Catalog_Model_Product_Option */
            $priceValue = 0;
            if ($option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                $_tmpPriceValues = array();
                foreach ($option->getValues() as $value) {
                    /* @var $value Mage_Catalog_Model_Product_Option_Value */
                    $id = $value->getId();
                    $_tmpPriceValues[$id] = $this->_getPriceConfiguration($value);
                }
                $priceValue = $_tmpPriceValues;
            } else {
                $priceValue = $this->_getPriceConfiguration($option);
            }
            $config[$option->getId()] = $priceValue;
        }

        return $this->_coreHelperData->jsonEncode($config);
    }

    /**
     * Get price configuration
     *
     * @param Mage_Catalog_Model_Product_Option_Value|Mage_Catalog_Model_Product_Option $option
     * @return array
     */
    protected function _getPriceConfiguration($option)
    {
        $data = array();
        $data['price']      = $this->_coreHelperData->currency($option->getPrice(true), false, false);
        $data['oldPrice']   = $this->_coreHelperData->currency($option->getPrice(false), false, false);
        $data['priceValue'] = $option->getPrice(false);
        $data['type']       = $option->getPriceType();
        $data['excludeTax'] = $price = $this->_taxHelperData->getPrice($option->getProduct(), $data['price'], false);
        $data['includeTax'] = $price = $this->_taxHelperData->getPrice($option->getProduct(), $data['price'], true);
        return $data;
    }
}