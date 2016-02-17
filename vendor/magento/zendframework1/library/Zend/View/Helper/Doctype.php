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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Registry */
#require_once 'Zend/Registry.php';

/** Zend_View_Helper_Abstract.php */
#require_once 'Zend/View/Helper/Abstract.php';

/**
 * Helper for setting and retrieving the doctype
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Doctype extends Zend_View_Helper_Abstract
{
    /**#@+
     * DocType constants
     */
    const XHTML11             = 'XHTML11';
    const XHTML1_STRICT       = 'XHTML1_STRICT';
    const XHTML1_TRANSITIONAL = 'XHTML1_TRANSITIONAL';
    const XHTML1_FRAMESET     = 'XHTML1_FRAMESET';
    const XHTML1_RDFA         = 'XHTML1_RDFA';
    const XHTML1_RDFA11       = 'XHTML1_RDFA11';
    const XHTML_BASIC1        = 'XHTML_BASIC1';
    const XHTML5              = 'XHTML5';
    const HTML4_STRICT        = 'HTML4_STRICT';
    const HTML4_LOOSE         = 'HTML4_LOOSE';
    const HTML4_FRAMESET      = 'HTML4_FRAMESET';
    const HTML5               = 'HTML5';
    const CUSTOM_XHTML        = 'CUSTOM_XHTML';
    const CUSTOM              = 'CUSTOM';
    /**#@-*/

    /**
     * Default DocType
     * @var string
     */
    protected $_defaultDoctype = self::HTML4_LOOSE;

    /**
     * Registry containing current doctype and mappings
     * @var ArrayObject
     */
    protected $_registry;

    /**
     * Registry key in which helper is stored
     * @var string
     */
    protected $_regKey = 'Zend_View_Helper_Doctype';

    /**
     * Constructor
     *
     * Map constants to doctype strings, and set default doctype
     *
     * @return void
     */
    public function __construct()
    {
        if (!Zend_Registry::isRegistered($this->_regKey)) {
            $this->_registry = new ArrayObject(array(
                'doctypes' => array(
                    self::XHTML11             => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
                    self::XHTML1_STRICT       => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
                    self::XHTML1_TRANSITIONAL => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
                    self::XHTML1_FRAMESET     => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
                    self::XHTML1_RDFA         => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">',
                    self::XHTML1_RDFA11       => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.1//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-2.dtd">',
                    self::XHTML_BASIC1        => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">',
                    self::XHTML5              => '<!DOCTYPE html>',
                    self::HTML4_STRICT        => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
                    self::HTML4_LOOSE         => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
                    self::HTML4_FRAMESET      => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
                    self::HTML5               => '<!DOCTYPE html>',
                )
            ));
            Zend_Registry::set($this->_regKey, $this->_registry);
            $this->setDoctype($this->_defaultDoctype);
        } else {
            $this->_registry = Zend_Registry::get($this->_regKey);
        }
    }

    /**
     * Set or retrieve doctype
     *
     * @param  string $doctype
     * @return Zend_View_Helper_Doctype
     */
    public function doctype($doctype = null)
    {
        if (null !== $doctype) {
            switch ($doctype) {
                case self::XHTML11:
                case self::XHTML1_STRICT:
                case self::XHTML1_TRANSITIONAL:
                case self::XHTML1_FRAMESET:
                case self::XHTML_BASIC1:
                case self::XHTML1_RDFA:
                case self::XHTML1_RDFA11:
                case self::XHTML5:
                case self::HTML4_STRICT:
                case self::HTML4_LOOSE:
                case self::HTML4_FRAMESET:
                case self::HTML5:
                    $this->setDoctype($doctype);
                    break;
                default:
                    if (substr($doctype, 0, 9) != '<!DOCTYPE') {
                        #require_once 'Zend/View/Exception.php';
                        $e = new Zend_View_Exception('The specified doctype is malformed');
                        $e->setView($this->view);
                        throw $e;
                    }
                    if (stristr($doctype, 'xhtml')) {
                        $type = self::CUSTOM_XHTML;
                    } else {
                        $type = self::CUSTOM;
                    }
                    $this->setDoctype($type);
                    $this->_registry['doctypes'][$type] = $doctype;
                    break;
            }
        }

        return $this;
    }

    /**
     * Set doctype
     *
     * @param  string $doctype
     * @return Zend_View_Helper_Doctype
     */
    public function setDoctype($doctype)
    {
        $this->_registry['doctype'] = $doctype;
        return $this;
    }

    /**
     * Retrieve doctype
     *
     * @return string
     */
    public function getDoctype()
    {
        return $this->_registry['doctype'];
    }

    /**
     * Get doctype => string mappings
     *
     * @return array
     */
    public function getDoctypes()
    {
        return $this->_registry['doctypes'];
    }

    /**
     * Is doctype XHTML?
     *
     * @return boolean
     */
    public function isXhtml()
    {
        return (stristr($this->getDoctype(), 'xhtml') ? true : false);
    }

    /**
     * Is doctype strict?
     *
     * @return boolean
     */
    public function isStrict()
    {
        switch ( $this->getDoctype() )
        {
            case self::XHTML1_STRICT:
            case self::XHTML11:
            case self::HTML4_STRICT:
                return true;
            default:
                return false;
        }
    }

    /**
     * Is doctype HTML5? (HeadMeta uses this for validation)
     *
     * @return booleean
     */
    public function isHtml5() {
        return (stristr($this->doctype(), '<!DOCTYPE html>') ? true : false);
    }

    /**
     * Is doctype RDFa?
     *
     * @return booleean
     */
    public function isRdfa() {
        return (stristr($this->getDoctype(), 'rdfa') ? true : false);
    }

    /**
     * String representation of doctype
     *
     * @return string
     */
    public function __toString()
    {
        $doctypes = $this->getDoctypes();
        return $doctypes[$this->getDoctype()];
    }
}
