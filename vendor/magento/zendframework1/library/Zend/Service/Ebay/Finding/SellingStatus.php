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
 * @version    $Id: SellingStatus.php 22791 2010-08-04 16:11:47Z renanbr $
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
class Zend_Service_Ebay_Finding_SellingStatus extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * The number of bids that have been placed on the item.
     *
     * @var integer
     */
    public $bidCount;

    /**
     * The listing's current price converted to the currency of the site
     * specified in the find request (globalId).
     *
     * @var float
     */
    public $convertedCurrentPrice;

    /**
     * The current price of the item given in the currency of the site on which
     * the item is listed.
     *
     * That is, currentPrice is returned in the original listing currency.
     *
     * For competitive-bid item listings, currentPrice is the current minimum
     * bid price if the listing has no bids, or the current high bid if the
     * listing has bids. A Buy It Now price has no effect on currentPrice.
     *
     * For Basic Fixed-Price (FixedPrice), Store Inventory (StoreInventory), and
     * Ad Format (AdFormat) listings, currentPrice is the current fixed price.
     *
     * @var float
     */
    public $currentPrice;

    /**
     * Specifies the listing's status in eBay's processing workflow.
     *
     * If an item's EndTime is in the past, but there are no details about the
     * buyer or high bidder (and the user is not anonymous), you can use
     * sellingState information to determine whether eBay has finished
     * processing the listing.
     *
     * Applicable values:
     *
     *     Active
     *     The listing is still live. It is also possible that the auction has
     *     recently ended, but eBay has not completed the final processing
     *     (e.g., the high bidder is still being determined).
     *
     *     Canceled
     *     The listing has been canceled by either the seller or eBay.
     *
     *     Ended
     *     The listing has ended and eBay has completed the processing of the
     *     sale (if any).
     *
     * @var string
     */
    public $sellingState;

    /**
     * Time left before the listing ends.
     *
     * The duration is represented in the ISO 8601 duration format
     * (PnYnMnDTnHnMnS). For listings that have ended, the time left is PT0S
     * (zero seconds). See the "duration" type for information about this time
     * format.
     *
     * @var string
     */
    public $timeLeft;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->bidCount              = $this->_query(".//$ns:bidCount[1]", 'integer');
        $this->convertedCurrentPrice = $this->_query(".//$ns:convertedCurrentPrice[1]", 'float');
        $this->currentPrice          = $this->_query(".//$ns:currentPrice[1]", 'float');
        $this->sellingState          = $this->_query(".//$ns:sellingState[1]", 'string');
        $this->timeLeft              = $this->_query(".//$ns:timeLeft[1]", 'string');

        $this->_attributes['convertedCurrentPrice'] = array(
            'currencyId' => $this->_query(".//$ns:convertedCurrentPrice[1]/@currencyId[1]", 'string')
        );

        $this->_attributes['currentPrice'] = array(
            'currencyId' => $this->_query(".//$ns:currentPrice[1]/@currencyId[1]", 'string')
        );
    }
}
