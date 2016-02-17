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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Filter_PregReplace
 */
#require_once 'Zend/Filter/Word/Separator/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Word_SeparatorToCamelCase extends Zend_Filter_Word_Separator_Abstract
{

    public function filter($value)
    {
        // a unicode safe way of converting characters to \x00\x00 notation
        $pregQuotedSeparator = preg_quote($this->_separator, '#');

        if (self::isUnicodeSupportEnabled()) {
            parent::setMatchPattern(array('#('.$pregQuotedSeparator.')(\p{L}{1})#','#(^\p{Ll}{1})#'));
            parent::setReplacement(array('Zend_Filter_Word_SeparatorToCamelCase', '_strtoupperArray'));
        } else {
            parent::setMatchPattern(array('#('.$pregQuotedSeparator.')([A-Za-z]{1})#','#(^[A-Za-z]{1})#'));
            parent::setReplacement(array('Zend_Filter_Word_SeparatorToCamelCase', '_strtoupperArray'));
        }

        return preg_replace_callback($this->_matchPattern, $this->_replacement, $value);
    }

    /**
     * @param array $matches
     * @return string
     */
    private static function _strtoupperArray(array $matches)
    {
        if (array_key_exists(2, $matches)) {
            return strtoupper($matches[2]);
        }
        return strtoupper($matches[1]);
    }

}
