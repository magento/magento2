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
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Position.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * Data model class to represent a playlist item's position in the list (yt:position)
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_Extension_Position extends Zend_Gdata_Extension
{

    protected $_rootElement = 'position';
    protected $_rootNamespace = 'yt';

    /**
     * Constructs a new Zend_Gdata_YouTube_Extension_Position object.
     *
     * @param string $value (optional) The 1-based position in the playlist
     */
    public function __construct($value = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct();
        $this->_text = $value;
    }

    /**
     * Get the value for the position in the playlist
     *
     * @return int The 1-based position in the playlist
     */
    public function getValue()
    {
        return $this->_text;
    }

    /**
     * Set the value for the position in the playlist
     *
     * @param int $value The 1-based position in the playlist
     * @return Zend_Gdata_Extension_Visibility The element being modified
     */
    public function setValue($value)
    {
        $this->_text = $value;
        return $this;
    }

    /**
     * Magic toString method allows using this directly via echo
     * Works best in PHP >= 4.2.0
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

}

