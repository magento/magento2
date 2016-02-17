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
 * @package    Zend_Gdata
 * @subpackage Analytics
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Feed
 */
#require_once 'Zend/Gdata/Feed.php';

/**
 * @see Zend_Gdata_Analytics
 */
#require_once 'Zend/Gdata/Analytics.php';

/**
 * @see Zend_Gdata_Geo_Entry
 */
#require_once 'Zend/Gdata/Analytics/DataEntry.php';

/**
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Analytics
 */
class Zend_Gdata_Analytics_DataFeed extends Zend_Gdata_Feed
{

    /**
     * The classname for individual feed elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Zend_Gdata_Analytics_DataEntry';
    /**
     * The classname for the feed.
     *
     * @var string
     */
    protected $_feedClassName = 'Zend_Gdata_Analytics_DataFeed';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Analytics::$namespaces);
        parent::__construct($element);
    }
}
