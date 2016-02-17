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
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Locale */
#require_once 'Zend/Locale.php';

/** Zend_View_Helper_Abstract.php */
#require_once 'Zend/View/Helper/Abstract.php';

/**
 * Translation view helper
 *
 * @category  Zend
 * @package   Zend_View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Translate extends Zend_View_Helper_Abstract
{
    /**
     * Translation object
     *
     * @var Zend_Translate_Adapter
     */
    protected $_translator;

    /**
     * Constructor for manually handling
     *
     * @param Zend_Translate|Zend_Translate_Adapter $translate Instance of Zend_Translate
     */
    public function __construct($translate = null)
    {
        if ($translate !== null) {
            $this->setTranslator($translate);
        }
    }

    /**
     * Translate a message
     * You can give multiple params or an array of params.
     * If you want to output another locale just set it as last single parameter
     * Example 1: translate('%1\$s + %2\$s', $value1, $value2, $locale);
     * Example 2: translate('%1\$s + %2\$s', array($value1, $value2), $locale);
     *
     * @param  string $messageid Id of the message to be translated
     * @return string|Zend_View_Helper_Translate Translated message
     */
    public function translate($messageid = null)
    {
        if ($messageid === null) {
            return $this;
        }

        $translate = $this->getTranslator();
        $options   = func_get_args();

        array_shift($options);
        $count  = count($options);
        $locale = null;
        if ($count > 0) {
            if (Zend_Locale::isLocale($options[($count - 1)], null, false) !== false) {
                $locale = array_pop($options);
            }
        }

        if ((count($options) === 1) and (is_array($options[0]) === true)) {
            $options = $options[0];
        }

        if ($translate !== null) {
            $messageid = $translate->translate($messageid, $locale);
        }

        if (count($options) === 0) {
            return $messageid;
        }

        return vsprintf($messageid, $options);
    }

    /**
     * Sets a translation Adapter for translation
     *
     * @param  Zend_Translate|Zend_Translate_Adapter $translate Instance of Zend_Translate
     * @throws Zend_View_Exception When no or a false instance was set
     * @return Zend_View_Helper_Translate
     */
    public function setTranslator($translate)
    {
        if ($translate instanceof Zend_Translate_Adapter) {
            $this->_translator = $translate;
        } else if ($translate instanceof Zend_Translate) {
            $this->_translator = $translate->getAdapter();
        } else {
            #require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('You must set an instance of Zend_Translate or Zend_Translate_Adapter');
            $e->setView($this->view);
            throw $e;
        }

        return $this;
    }

    /**
     * Retrieve translation object
     *
     * @return Zend_Translate_Adapter|null
     */
    public function getTranslator()
    {
        if ($this->_translator === null) {
            #require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Translate')) {
                $this->setTranslator(Zend_Registry::get('Zend_Translate'));
            }
        }

        return $this->_translator;
    }

    /**
     * Set's an new locale for all further translations
     *
     * @param  string|Zend_Locale $locale New locale to set
     * @throws Zend_View_Exception When no Zend_Translate instance was set
     * @return Zend_View_Helper_Translate
     */
    public function setLocale($locale = null)
    {
        $translate = $this->getTranslator();
        if ($translate === null) {
            #require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('You must set an instance of Zend_Translate or Zend_Translate_Adapter');
            $e->setView($this->view);
            throw $e;
        }

        $translate->setLocale($locale);
        return $this;
    }

    /**
     * Returns the set locale for translations
     *
     * @throws Zend_View_Exception When no Zend_Translate instance was set
     * @return string|Zend_Locale
     */
    public function getLocale()
    {
        $translate = $this->getTranslator();
        if ($translate === null) {
            #require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('You must set an instance of Zend_Translate or Zend_Translate_Adapter');
            $e->setView($this->view);
            throw $e;
        }

        return $translate->getLocale();
    }
}
