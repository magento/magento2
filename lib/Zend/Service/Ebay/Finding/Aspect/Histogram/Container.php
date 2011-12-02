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
 * @version    $Id: Container.php 22791 2010-08-04 16:11:47Z renanbr $
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
class Zend_Service_Ebay_Finding_Aspect_Histogram_Container extends Zend_Service_Ebay_Finding_Abstract
{
    /**
     * A characteristic of an item in a domain.
     *
     * For example, "Optical Zoom", "Brand", and "Megapixels" could be aspects
     * of the Digital Cameras domain. Aspects are well-known, standardized
     * characteristics of a domain, and they vary from domain to domain (the
     * aspects of "Men's Shoes" are different from those of "Digital Cameras").
     * A search request on the eBay site will often display aspects and their
     * respective aspect values on the left-had side of a query response.
     *
     * Aspects are extracted from item listing properties (such as item titles
     * and subtitles), and represent the characteristics of active items. Values
     * returned in the Aspect container can be used as inputs to the
     * aspectFilter fields in a query to distill the items returned by the
     * query. eBay generates aspects dynamically from the items currently
     * listed; aspects provide a view into what is currently available on eBay.
     * Because of this, aspect values returned one day cannot be guaranteed to
     * be valid the next day.
     *
     * @var Zend_Service_Ebay_Finding_Aspect_Set
     */
    public $aspect;

    /**
     * A buy-side group of items, for example "Shoes.".
     *
     * Domains are extracted from item listing properties, such as the title,
     * descriptions, and so on.
     *
     * @var string
     */
    public $domainDisplayName;

    /**
     * A buy-side group of items that share aspects, but not necessarily an eBay
     * category.
     *
     * For example "Women's Dresses" or "Digital Cameras" could be domains. You
     * can use a domainName to label a set of aspects that you display.
     *
     * @var string
     */
    public $domainName;

    /**
     * @return void
     */
    protected function _init()
    {
        parent::_init();
        $ns = Zend_Service_Ebay_Finding::XMLNS_FINDING;

        $this->domainDisplayName = $this->_query(".//$ns:domainDisplayName[1]", 'string');
        $this->domainName        = $this->_query(".//$ns:domainName[1]", 'string');

        $this->_attributes['aspect'] = array(
            'name' => $this->_query(".//$ns:aspect/@name", 'string', true)
        );

        $nodes = $this->_xPath->query(".//$ns:aspect", $this->_dom);
        if ($nodes->length > 0) {
            /**
             * @see Zend_Service_Ebay_Finding_Aspect_Set
             */
            #require_once 'Zend/Service/Ebay/Finding/Aspect/Set.php';
            $this->aspect = new Zend_Service_Ebay_Finding_Aspect_Set($nodes);
        }
    }
}
