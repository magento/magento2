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
 * @see Zend_Gdata_Extension_Property
 */
#require_once 'Zend/Gdata/Analytics/Extension/Property.php';

/**
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Analytics
 */
class Zend_Gdata_Analytics_Extension_Metric
    extends Zend_Gdata_Analytics_Extension_Property
{
    protected $_rootNamespace = 'ga';
    protected $_rootElement = 'metric';
    protected $_value = null;
    protected $_name = null;

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
            case 'name':
                $this->_name = $attribute->nodeValue;
                break;
            case 'value':
                $this->_value = $attribute->nodeValue;
                break;
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }
}
