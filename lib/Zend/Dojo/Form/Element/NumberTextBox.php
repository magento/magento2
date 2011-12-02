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
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Dojo_Form_Element_ValidationTextBox */
#require_once 'Zend/Dojo/Form/Element/ValidationTextBox.php';

/**
 * NumberTextBox dijit
 *
 * @uses       Zend_Dojo_Form_Element_ValidationTextBox
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: NumberTextBox.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Dojo_Form_Element_NumberTextBox extends Zend_Dojo_Form_Element_ValidationTextBox
{
    /**
     * Use NumberTextBox dijit view helper
     * @var string
     */
    public $helper = 'NumberTextBox';

    /**
     * Allowed numeric type formats
     * @var array
     */
    protected $_allowedTypes = array(
        'decimal',
        'scientific',
        'percent',
        'currency',
    );

    /**
     * Set locale
     *
     * @param  string $locale
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setLocale($locale)
    {
        $this->setConstraint('locale', (string) $locale);
        return $this;
    }

    /**
     * Retrieve locale
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->getConstraint('locale');
    }

    /**
     * Set numeric format pattern
     *
     * @param  string $pattern
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setPattern($pattern)
    {
        $this->setConstraint('pattern', (string) $pattern);
        return $this;
    }

    /**
     * Retrieve numeric format pattern
     *
     * @return string|null
     */
    public function getPattern()
    {
        return $this->getConstraint('pattern');
    }

    /**
     * Set numeric format type
     *
     * @see    $_allowedTypes
     * @param  string $type
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setType($type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->_allowedTypes)) {
            #require_once 'Zend/Form/Element/Exception.php';
            throw new Zend_Form_Element_Exception(sprintf('Invalid numeric type "%s" specified', $type));
        }

        $this->setConstraint('type', $type);
        return $this;
    }

    /**
     * Retrieve type
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->getConstraint('type');
    }

    /**
     * Set decimal places
     *
     * @param  int $places
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setPlaces($places)
    {
        $this->setConstraint('places', (int) $places);
        return $this;
    }

    /**
     * Retrieve decimal places
     *
     * @return int|null
     */
    public function getPlaces()
    {
        return $this->getConstraint('places');
    }

    /**
     * Set strict flag
     *
     * @param  bool $strict
     * @return Zend_Dojo_Form_Element_NumberTextBox
     */
    public function setStrict($flag)
    {
        $this->setConstraint('strict', (bool) $flag);
        return $this;
    }

    /**
     * Retrieve strict flag
     *
     * @return bool
     */
    public function getStrict()
    {
        if (!$this->hasConstraint('strict')) {
            return false;
        }
        return ('true' == $this->getConstraint('strict'));
    }
}
