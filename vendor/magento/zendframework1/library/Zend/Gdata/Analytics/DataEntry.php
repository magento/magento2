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
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Analytics
 */
class Zend_Gdata_Analytics_DataEntry extends Zend_Gdata_Entry
{
    /**
     * @var array
     */
    protected $_dimensions = array();
    /**
     * @var array
     */
    protected $_metrics = array();

    /**
     * @param DOMElement $element
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
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('analytics') . ':' . 'dimension';
                $dimension = new Zend_Gdata_Analytics_Extension_Dimension();
                $dimension->transferFromDOM($child);
                $this->_dimensions[] = $dimension;
                break;
            case $this->lookupNamespace('analytics') . ':' . 'metric';
                $metric = new Zend_Gdata_Analytics_Extension_Metric();
                $metric->transferFromDOM($child);
                $this->_metrics[] = $metric;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getDimension($name)
    {
        foreach ($this->_dimensions as $dimension) {
            if ($dimension->getName() == $name) {
                return $dimension;
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getMetric($name)
    {
        foreach ($this->_metrics as $metric) {
            if ($metric->getName() == $name) {
                return $metric;
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getValue($name)
    {
        if (null !== ($metric = $this->getMetric($name))) {
            return $metric;
        }
        return $this->getDimension($name);
    }
}
