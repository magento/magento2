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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SellerInfo.php 22791 2010-08-04 16:11:47Z renanbr $
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
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @uses       Zend_Service_Ebay_Finding_Abstract
 */
class Zend_Service_Ebay_Finding_SellerInfo extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * Visual indicator of user's feedback score.
     *
     * Applicable values:
     *
     *     None
     *     No graphic displayed, feedback score 0-9.
     *
     *     Yellow
     *     Yellow Star, feedback score 10-49.
     *
     *     Blue
     *     Blue Star, feedback score 50-99.
     *
     *     Turquoise
     *     Turquoise Star, feedback score 100-499.
     *
     *     Purple
     *     Purple Star, feedback score 500-999.
     *
     *     Red
     *     Red Star, feedback score 1,000-4,999.
     *
     *     Green
     *     Green Star, feedback score 5,000-9,999.
     *
     *     YellowShooting
     *     Yellow Shooting Star, feedback score 10,000-24,999.
     *
     *     TurquoiseShooting
     *     Turquoise Shooting Star, feedback score 25,000-49,999.
     *
     *     PurpleShooting
     *     Purple Shooting Star, feedback score 50,000-99,999.
     *
     *     RedShooting
     *     Red Shooting Star, feedback score 100,000-499,000 and above.
     *
     *     GreenShooting
     *     Green Shooting Star, feedback score 500,000-999,000 and above.
     *
     *     SilverShooting
     *     Silver Shooting Star, feedback score 1,000,000 or more.
     *
     * @var string
     */
    public $feedbackRatingStar;

    /**
     * The aggregate feedback score of the seller.
     *
     * A seller's feedback score is their net positive feedback minus their net
     * negative feedback. Feedback scores are a quantitative expression of the
     * desirability of dealing with a seller in a transaction.
     *
     * @var integer
     */
    public $feedbackScore;

    /**
     * The percentage value of a user's positive feedback (their positive
     * feedbackScore divided by their total positive plus negative feedback).
     *
     * @var float
     */
    public $positiveFeedbackPercent;

    /**
     * The seller's eBay user name; a unique value.
     *
     * @var string
     */
    public $sellerUserName;

    /**
     * Indicates whether the seller of the item is top-rated.
     *
     * A top-rated seller:
     *     - Consistently receives highest buyers' ratings
     *     - Ships items quickly
     *     - Has earned a track record of excellent service
     *
     * eBay regularly reviews the performance of these sellers to confirm they
     * continue to meet the program's requirements.
     *
     * This field is returned for the following sites only: US (EBAY-US), Motors
     * (EBAY-MOTOR), DE (EBAY-DE), AT (EBAY-AT), and CH (EBAY-CH).
     *
     * @var boolean
     */
    public $topRatedSeller;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->feedbackRatingStar      = $this->_query(".//$ns:feedbackRatingStar[1]", 'string');
        $this->feedbackScore           = $this->_query(".//$ns:feedbackScore[1]", 'integer');
        $this->positiveFeedbackPercent = $this->_query(".//$ns:positiveFeedbackPercent[1]", 'float');
        $this->sellerUserName          = $this->_query(".//$ns:sellerUserName[1]", 'string');
        $this->topRatedSeller          = $this->_query(".//$ns:topRatedSeller[1]", 'boolean');
    }
}
