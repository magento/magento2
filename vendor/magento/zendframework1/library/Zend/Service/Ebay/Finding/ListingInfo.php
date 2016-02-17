<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ListingInfo.php 22791 2010-08-04 16:11:47Z renanbr $
 */

/**
 * @see Zend_Service_Ebay_Finding_Abstract
 */
#require_once 'Zend/Service/Ebay/Finding/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Ebay
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Abstract
 */
class Zend_Service_Ebay_Finding_ListingInfo extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * Shows whether or not the seller will accept a best offer for the
     * associated item.
     *
     * Best Offer allows a buyer to make a lower-priced binding offer on a fixed
     * price item. Buyers cannot see how many offers have been made (only the
     * seller can see this information). To make a best offer on a listing, use
     * the eBay Web site.
     *
     * @var boolean
     */
    public $bestOfferEnabled;

    /**
     * Used with competitive-bid auctions, the associated item includes a Buy It
     * Now option if this value returns true.
     *
     * Buy It Now lets a user purchase the item at a fixed price, effectively
     * ending the auction. On most sites, the Buy It Now option is removed (and
     * this value returns false) once a valid bid is made on the associated item
     * (a valid bid could be a bid above the reserve price).
     *
     * @var boolean
     */
    public $buyItNowAvailable;

    /**
     * The Buy It Now Price of the item (if any), in the currency of the site on
     * which the item was listed.
     *
     * You can use this field to determine if the item was originally listed
     * with Buy It Now, even if the Buy It Now option is no longer available for
     * the item.
     *
     * For Basic Fixed-Price (FixedPrice), Store Inventory (StoreInventory), and
     * Ad Format (AdFormat) listings, currentPrice is the current fixed price.
     *
     * Only returned if an item was listed with Buy It Now.
     *
     * @var float
     */
    public $buyItNowPrice;

    /**
     * The listing's Buy It Now Price (if any), converted into the currency of
     * the site to which you sent your search request.
     *
     * For active items, refresh this value every 24 hours to pick up changes in
     * conversion rates (if this value has been converted).
     *
     * Price fields are returned as doubles, not necessarily in the traditional
     * monetary format of the site's country. For example, a US Dollar value
     * might be returned as 3.880001 instead of 3.88.
     *
     * Only returned if an item was listed with Buy It Now.
     *
     * @var float
     */
    public $convertedBuyItNowPrice;

    /**
     * Time stamp specifying when the listing is scheduled to end, or the actual
     * end time if the item listing has ended.
     *
     * This value is returned in GMT, the ISO 8601 date and time format
     * (YYYY-MM-DDTHH:MM:SS.SSSZ). See the "dateTime" type for information about
     * the time format, and for details on converting to and from the GMT time
     * zone.
     *
     * @var integer
     */
    public $endTime;

    /**
     * If true, a generic gift icon displays next the listing's title in search
     * and browse pages.
     *
     * @var boolean
     */
    public $gift;

    /**
     * The format of the listing, such as online auction, fixed price, or
     * advertisement.
     *
     * Applicable values:
     *
     *     AdFormat
     *     Advertisement to solicit inquiries on listings such as real estate.
     *     Permits no bidding on that item, service, or property. To express
     *     interest, a buyer fills out a contact form that eBay forwards to the
     *     seller as a lead. This format does not enable buyers and sellers to
     *     transact online through eBay and eBay Feedback is not available for
     *     ad format listings.
     *
     *     Auction
     *     Competitive-bid online auction format. Buyers engage in competitive
     *     bidding, although Buy It Now may be offered as long as no valid bids
     *     have been placed. Online auctions are listed on eBay.com; they can
     *     also be listed in a seller's eBay Store if the seller is a Store
     *     owner.
     *
     *     AuctionWithBIN
     *     Same as Auction format, but Buy It Now is enabled. AuctionWithBIN
     *     changes to Auction if a valid bid has been placed on the item. Valid
     *     bids include bids that are equal to or above any specified reserve
     *     price.
     *
     *     Classified
     *     Classified Ads connect buyers and sellers, who then complete the sale
     *     outside of eBay. This format does not enable buyers and sellers to
     *     transact online through eBay and eBay Feedback is not available for
     *     these listing types.
     *
     *     FixedPrice
     *     A fixed-price listing. Auction-style bidding is not allowed. On some
     *     sites, this auction format is also known as "Buy It Now Only" (not to
     *     be confused with the Buy It Now option available with
     *     competitive-bidding auctions). Fixed-price listings appear on
     *     eBay.com; they can also be listed in a seller's eBay Store if the
     *     seller is a Store owner.
     *
     *     StoreInventory
     *     A fixed-price format for eBay Store sellers. Store Inventory listings
     *     appear after other listings in regular browse and search item
     *     listings on eBay. Store items have a lower Insertion Fee and longer
     *     listing durations. This selling type can only be specified by sellers
     *     who have an eBay Store. Store Inventory listings are listed on
     *     eBay.com as well as in the seller's eBay Store.
     *
     * @var string
     */
    public $listingType;

    /**
     * Time stamp that eBay recorded as the moment the listing was made
     * available.
     *
     * This value is returned in GMT, the ISO 8601 date and time format
     * (YYYY-MM-DDTHH:MM:SS.SSSZ). See the "dateTime" type for information about
     * the time format, and for details on converting to and from the GMT time
     * zone. Note that it is possible for startTime to be different from the
     * value returned by GetSingleItem.
     *
     * @var integer
     */
    public $startTime;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->bestOfferEnabled       = $this->_query(".//$ns:bestOfferEnabled[1]", 'boolean');
        $this->buyItNowAvailable      = $this->_query(".//$ns:buyItNowAvailable[1]", 'boolean');
        $this->buyItNowPrice          = $this->_query(".//$ns:buyItNowPrice[1]", 'float');
        $this->convertedBuyItNowPrice = $this->_query(".//$ns:convertedBuyItNowPrice[1]", 'float');
        $this->endTime                = $this->_query(".//$ns:endTime[1]", 'string');
        $this->gift                   = $this->_query(".//$ns:gift[1]", 'boolean');
        $this->listingType            = $this->_query(".//$ns:listingType[1]", 'string');
        $this->startTime              = $this->_query(".//$ns:startTime[1]", 'string');

        $this->_attributes['buyItNowPrice'] = array(
            'currencyId' => $this->_query(".//$ns:buyItNowPrice[1]/@currencyId[1]", 'string')
        );

        $this->_attributes['convertedBuyItNowPrice'] = array(
            'currencyId' => $this->_query(".//$ns:convertedBuyItNowPrice[1]/@currencyId[1]", 'string')
        );
    }
}
