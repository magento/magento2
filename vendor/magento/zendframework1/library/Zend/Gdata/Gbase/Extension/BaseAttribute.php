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
 * @subpackage Gbase
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_App_Extension_Element
 */
#require_once 'Zend/Gdata/App/Extension/Element.php';

/**
 * Concrete class for working with ItemType elements.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gbase
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gbase_Extension_BaseAttribute extends Zend_Gdata_App_Extension_Element
{
    /**
     * Create a new instance.
     *
     * @param string $name (optional) The name of the Base attribute
     * @param string $text (optional) The text value of the Base attribute
     * @param string $text (optional) The type of the Base attribute
     */
    public function __construct($name = null, $text = null, $type = null)
    {
        throw new Zend_Exception(
            'Google Base API has been discontinued by Google and was removed'
            . ' from Zend Framework in 1.12.0.  For more information see: '
            . 'http://googlemerchantblog.blogspot.ca/2010/12/new-shopping-apis-and-deprecation-of.html'
        );
    }
}
