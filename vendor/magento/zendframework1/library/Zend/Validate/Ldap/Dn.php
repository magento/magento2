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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Abstract.php 24807 2012-05-15 12:10:42Z adamlundrigan $
 */

/**
 * @see Zend_Validate_Interface
 */
#require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Ldap_Dn extends Zend_Validate_Abstract
{

    const MALFORMED = 'malformed';

    /**
     * Validation failure message template definitions.
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::MALFORMED => 'DN is malformed',
    );

    /**
     * Defined by Zend_Validate_Interface.
     *
     * Returns true if and only if $value is a valid DN.
     *
     * @param string $value The value to be validated.
     *
     * @return boolean
     */
    public function isValid($value)
    {
        $valid = Zend_Ldap_Dn::checkDn($value);
        if ($valid === false) {
            $this->_error(self::MALFORMED);
            return false;
        }
        return true;
    }
}
