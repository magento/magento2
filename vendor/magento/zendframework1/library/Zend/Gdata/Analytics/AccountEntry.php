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
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Analytics_Extension_Dimension
 */
#require_once 'Zend/Gdata/Analytics/Extension/Dimension.php';

/**
 * @see Zend_Gdata_Analytics_Extension_Metric
 */
#require_once 'Zend/Gdata/Analytics/Extension/Metric.php';

/**
 * @see Zend_Gdata_Analytics_Extension_Property
 */
#require_once 'Zend/Gdata/Analytics/Extension/Property.php';

/**
 * @see Zend_Gdata_Analytics_Extension_TableId
 */
#require_once 'Zend/Gdata/Analytics/Extension/TableId.php';

/**
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Analytics
 */
class Zend_Gdata_Analytics_AccountEntry extends Zend_Gdata_Entry
{
    protected $_accountId;
    protected $_accountName;
    protected $_profileId;
    protected $_webPropertyId;
    protected $_currency;
    protected $_timezone;
    protected $_tableId;
    protected $_profileName;
    protected $_goal;

    /**
     * @see Zend_Gdata_Entry::__construct()
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Analytics::$namespaces);
        parent::__construct($element);
    }

    /**
     * @param DOMElement $child
     * @return void
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName){
            case $this->lookupNamespace('analytics') . ':' . 'property';
                $property = new Zend_Gdata_Analytics_Extension_Property();
                $property->transferFromDOM($child);
                $this->{$property->getName()} = $property;
                break;
            case $this->lookupNamespace('analytics') . ':' . 'tableId';
                $tableId = new Zend_Gdata_Analytics_Extension_TableId();
                $tableId->transferFromDOM($child);
                $this->_tableId = $tableId;
                break;
            case $this->lookupNamespace('ga') . ':' . 'goal';
                $goal = new Zend_Gdata_Analytics_Extension_Goal();
                $goal->transferFromDOM($child);
                $this->_goal = $goal;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }
}
