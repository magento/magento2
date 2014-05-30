<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http:framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http:framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Item.php 22824 2010-08-09 18:59:54Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Finding_Abstract
 */
#require_once 'Zend/Service/Ebay/Finding/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http:framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Abstract
 */
class Zend_Service_Ebay_Finding_Search_Item extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * If true, the seller requires immediate payment for the item. If false (or
     * not specified), immediate payment is not requested. Buyers must have a
     * PayPal account to purchase items that require immediate payment.
     *
     * A seller can choose to require immediate payment for Fixed Price and Buy
     * It Now listings, including eBay Stores Inventory listings. If a Buy It
     * Now item ends as an auction (that is, if the Buy It Now option is removed
     * due to bids being placed on the listing), the immediate payment
     * requirement does not apply.
     *
     * @var boolean
     */
    public $autoPay;

    /**
     * A unique identification number assigned by eBay to registered nonprofit
     * charity organizations.
     *
     * @var integer
     */
    public $charityId;

    /**
     * Two-letter ISO 3166 country code to indicate the country where the item
     * is located.
     *
     * @link http://www.iso.org/iso/country_codes/iso_3166_code_lists/english_country_names_and_code_elements.htm
     * @var  string
     */
    public $country;

    /**
     * The distance that the item is from the buyer, calculated usin
     *  buyerPostalCode.
     *
     *  The unit for distance varies by site, and is either miles or kilometers.
     *  If the country whose site you are searching uses kilometers to measure
     *  distance (for example, India/EBAY-IN), the unit is kilometers. If the
     *  site is either the US or UK, the distance unit is miles.
     *
     *  This value is only returned for distance-based searches. You must
     *  specify a buyerPostalCode and either sort by Distance, or use a
     *  combination of the MaxDistance LocalSearch itemFilters.
     *
     * @var float
     */
    public $distance;

    /**
     * URL for the Gallery Plus image.
     *
     * The size of Gallery Plus images (up to 400 x 400 pixels) is bigger than
     * the size of standard gallery images. In site search results, you can view
     * the Gallery Plus image by hovering over or clicking the Enlarge link or
     * magifying glass icon. The image uses one of the following graphics
     * formats: JPEG, BMP, TIFF, or GIF. This field is only returned when the
     * seller has opted for the Gallery Plus option for the given item.
     *
     * @var string[]
     */
    public $galleryPlusPictureURL;

    /**
     * URL for the Gallery thumbnail image.
     *
     * The image must be provided in one of the following graphics formats:
     * JPEG, BMP, TIF, or GIF. Returned only if the seller chose to show a
     * gallery image.
     *
     * @var string
     */
    public $galleryURL;

    /**
     * The identifier for the site on which the item is listed.
     *
     * Returns a Global ID, which is a unique identifier that specifies the
     * combination of the site, language, and territory. In other eBay APIs
     * (such as the Shopping API), this value is know as the site ID.
     *
     * @link http://developer.ebay.com/DevZone/finding/CallRef/Enums/GlobalIdList.html
     * @var  string
     */
    public $globalId;

    /**
     * The ID that uniquely identifies the item listing.
     *
     * eBay generates this ID when an item is listed. ID values are unique
     * across all eBay sites.
     *
     * @var string
     */
    public $itemId;

    /**
     * The format type of the listing, such as online auction, fixed price, or
     * advertisement.
     *
     * @var Zend_Service_Ebay_Finding_ListingInfo
     */
    public $listingInfo;

    /**
     * Physical location of the item, as specified by the seller.
     *
     * This gives a general indication from where the item will be shipped (or
     * delivered).
     *
     * @var string
     */
    public $location;

    /**
     * Identifies the payment method (or methods) the seller will accept for the
     * item (such as PayPal).
     *
     *  Payment methods are not applicable to eBay Real Estate advertisement
     *  listings or other Classified Ad listing formats.
     *
     * @link http://developer.ebay.com/DevZone/shopping/docs/CallRef/types/BuyerPaymentMethodCodeType.html
     * @var  string[]
     */
    public $paymentMethod;

    /**
     * The postal code where the listed item is located.
     *
     * This field is returned only if a postal code has been specified by the
     * seller. eBay proximity and local search behavior can use the combination
     * of buyerPostalCode and postalCode values.
     *
     * @var string
     */
    public $postalCode;

    /**
     * Details about the first (or only) category in which the item is listed.
     *
     * Note that items can be listed in more than a single category.
     *
     * @var Zend_Service_Ebay_Finding_Category
     */
    public $primaryCategory;

    /**
     * Unique identifier for the eBay catalog product with which the item was
     * listed.
     *
     * An eBay catalog product consists of pre-filled Item Specifics, additional
     * descriptive information, plus a stock photo (if available). These product
     * details are used to pre-fill item information, which is used to describe
     * the item and can also help surface the item in searches.
     *
     * eBay supports the following types of product ID types: ISBN, UPC, EAN,
     * and ReferenceID (ePID, also known as an eBay Product Reference ID).
     * ReferenceID values are returned when available. A UPC, ISBN, or EAN
     * product identifier will be returned only when a ReferenceID is not
     * available.
     *
     * This productId value can be used as input with findItemsByProduct to
     * retrieve items that were listed with the specified eBay catalog product.
     *
     * This field is only returned when a product was used to list the item.
     *
     * @var string
     */
    public $productId;

    /**
     * Details about the second category in which the item is listed.
     *
     * This element is not returned if the seller did not specify a secondary
     * category.
     *
     * @var Zend_Service_Ebay_Finding_Category
     */
    public $secondaryCategory;

    /**
     * Information about the item's seller.
     *
     * Only returned if SellerInfo is specified in the outputSelector field in
     * the request.
     *
     * @var Zend_Service_Ebay_Finding_SellerInfo
     */
    public $sellerInfo;

    /**
     * Specifies the item's selling status with regards to eBay's processing
     * workflow.
     *
     * @var Zend_Service_Ebay_Finding_SellingStatus
     */
    public $sellingStatus;

    /**
     * Container for data about a listing's shipping details.
     *
     * @var Zend_Service_Ebay_Finding_ShippingInfo
     */
    public $shippingInfo;

    /**
     * Information about the eBay store in which the item is listed.
     *
     * Only returned if the item is listed in a store and StoreInfo is specified
     * in the outputSelector field in the request.
     *
     * @var Zend_Service_Ebay_Finding_Storefront
     */
    public $storeInfo;

    /**
     * Subtitle of the item.
     *
     * Only returned if the seller included a subtitle for the listing.
     *
     * @var string
     */
    public $subtitle;

    /**
     * Name of the item as it appears in the listing title, or in search and
     * browse results.
     *
     * @var string
     */
    public $title;

    /**
     * The URL to view this specific listing on eBay.
     *
     * The returned URL is optimized to support natural search. That is, the URL
     * is designed to make items on eBay easier to find via popular internet
     * search engines. The URL includes the item title along with other
     * optimizations. To note, "?" (question mark) optimizes to "_W0QQ", "&"
     * (ampersand) optimizes to "QQ", and "=" (equals sign) optimizes to "Z".
     *
     * Do not modify the returned URLs in your application queries (for example,
     * eBay won't recognize the URL if you change QQ to &). In the Sandbox
     * environment (and on the Hong Kong site), the data returned in this field
     * is a standard ViewItem URL rather than the ViewItemURL for natural
     * search.
     *
     * @var string
     */
    public $viewItemURL;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->autoPay               = $this->_query(".//$ns:autoPay[1]", 'boolean');
        $this->charityId             = $this->_query(".//$ns:charityId[1]", 'integer');
        $this->country               = $this->_query(".//$ns:country[1]", 'string');
        $this->distance              = $this->_query(".//$ns:distance[1]", 'float');
        $this->galleryPlusPictureURL = $this->_query(".//$ns:galleryPlusPictureURL", 'string', true);
        $this->galleryURL            = $this->_query(".//$ns:galleryURL[1]", 'string');
        $this->globalId              = $this->_query(".//$ns:globalId[1]", 'string');
        $this->itemId                = $this->_query(".//$ns:itemId[1]", 'string');
        $this->location              = $this->_query(".//$ns:location[1]", 'string');
        $this->paymentMethod         = $this->_query(".//$ns:paymentMethod", 'string', true);
        $this->postalCode            = $this->_query(".//$ns:postalCode[1]", 'string');
        $this->productId             = $this->_query(".//$ns:productId[1]", 'string');
        $this->subtitle              = $this->_query(".//$ns:subtitle[1]", 'string');
        $this->title                 = $this->_query(".//$ns:title[1]", 'string');
        $this->viewItemURL           = $this->_query(".//$ns:viewItemURL[1]", 'string');

        $this->_attributes['distance'] = array(
            'unit' => $this->_query(".//$ns:distance[1]/@unit[1]", 'string')
        );
        $this->_attributes['productId'] = array(
            'type' => $this->_query(".//$ns:productId[1]/@type[1]", 'string')
        );

        $node = $this->_xPath->query(".//$ns:listingInfo[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_ListingInfo
             */
            #require_once 'Zend/Service/Ebay/Finding/ListingInfo.php';
            $this->listingInfo = new Zend_Service_Ebay_Finding_ListingInfo($node);
        }

        $node = $this->_xPath->query(".//$ns:primaryCategory[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_Category
             */
            #require_once 'Zend/Service/Ebay/Finding/Category.php';
            $this->primaryCategory = new Zend_Service_Ebay_Finding_Category($node);
        }

        $node = $this->_xPath->query(".//$ns:secondaryCategory[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_Category
             */
            #require_once 'Zend/Service/Ebay/Finding/Category.php';
            $this->secondaryCategory = new Zend_Service_Ebay_Finding_Category($node);
        }

        $node = $this->_xPath->query(".//$ns:sellerInfo[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_SellerInfo
             */
            #require_once 'Zend/Service/Ebay/Finding/SellerInfo.php';
            $this->sellerInfo = new Zend_Service_Ebay_Finding_SellerInfo($node);
        }

        $node = $this->_xPath->query(".//$ns:sellingStatus[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_SellingStatus
             */
            #require_once 'Zend/Service/Ebay/Finding/SellingStatus.php';
            $this->sellingStatus = new Zend_Service_Ebay_Finding_SellingStatus($node);
        }

        $node = $this->_xPath->query("./$ns:shippingInfo", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_ShippingInfo
             */
            #require_once 'Zend/Service/Ebay/Finding/ShippingInfo.php';
            $this->shippingInfo = new Zend_Service_Ebay_Finding_ShippingInfo($node);
        }

        $node = $this->_xPath->query(".//$ns:storeInfo[1]", $this->_dom)->item(0);
        if ($node) {
            /**
             * @see Zend_Service_Ebay_Finding_Storefront
             */
            #require_once 'Zend/Service/Ebay/Finding/Storefront.php';
            $this->storeInfo = new Zend_Service_Ebay_Finding_Storefront($node);
        }
    }

    /**
     * @param  Zend_Service_Ebay_Finding $proxy
     * @param  Zend_Config|array         $options
     * @return Zend_Service_Ebay_Finding_Response_Items
     */
    public function findItemsByProduct(Zend_Service_Ebay_Finding $proxy, $options = null)
    {
        $type = $this->attributes('productId', 'type');
        return $proxy->findItemsByProduct($this->productId, $type, $options);
    }
}
