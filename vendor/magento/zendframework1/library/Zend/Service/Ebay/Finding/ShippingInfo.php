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
 * @version    $Id: ShippingInfo.php 22791 2010-08-04 16:11:47Z renanbr $
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
class Zend_Service_Ebay_Finding_ShippingInfo extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * The basic shipping cost of the item.
     *
     * @var float
     */
    public $shippingServiceCost;

    /**
     * The shipping method that was used for determining the cost of shipping.
     *
     * For example: flat rate, calculated, or free. The seller specifies the
     * available shipping services when they list the item.
     *
     * Applicable values:
     *
     *     Calculated
     *     The calculated shipping model: The posted cost of shipping is based
     *     on the buyer-selected shipping service, chosen by the buyer from the
     *     different shipping services offered by the seller. The shipping costs
     *     are calculated by eBay and the shipping carrier, based on the buyer's
     *     address. Any packaging and handling costs established by the seller
     *     are automatically rolled into the total.
     *
     *     CalculatedDomesticFlatInternational
     *     The seller specified one or more calculated domestic shipping
     *     services and one or more flat international shipping services.
     *
     *     Flat
     *     The flat-rate shipping model: The seller establishes the cost of
     *     shipping and any shipping insurance, regardless of what any
     *     buyer-selected shipping service might charge the seller.
     *
     *     FlatDomesticCalculatedInternational
     *     The seller specified one or more flat domestic shipping services and
     *     one or more calculated international shipping services.
     *
     *     Free
     *     Free is used when the seller has declared that shipping is free for
     *     the buyer.
     *
     *     FreePickup
     *     No shipping available, the buyer must pick up the item from the
     *     seller.
     *
     *     Freight
     *     The freight shipping model: the cost of shipping is determined by a
     *     third party, FreightQuote.com, based on the buyer's address (postal
     *     code).
     *
     *     FreightFlat
     *     The flat rate shipping model: the seller establishes the cost of
     *     freight shipping and freight insurance, regardless of what any
     *     buyer-selected shipping service might charge the seller.
     *
     *     NotSpecified
     *     The seller did not specify the shipping type.
     *
     * @var string
     */
    public $shippingType;

    /**
     * An international location or region to which the seller is willing to
     * ship the item.
     *
     * Returned only for items that have shipToLocations specified.
     *
     * @link http://developer.ebay.com/DevZone/finding/CallRef/Enums/shipToLocationList.html
     * @var  string[]
     */
    public $shipToLocations;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->shippingServiceCost = $this->_query(".//$ns:shippingServiceCost[1]", 'float');
        $this->shippingType        = $this->_query(".//$ns:shippingType[1]", 'string');
        $this->shipToLocations     = $this->_query(".//$ns:shipToLocations", 'string', true);

        $this->_attributes['shippingServiceCost'] = array(
            'currencyId' => $this->_query(".//$ns:shippingServiceCost[1]/@currencyId[1]", 'string')
        );
    }
}
