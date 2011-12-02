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

/** Zend_Dojo_Form_Element_TextBox */
#require_once 'Zend/Dojo/Form/Element/TextBox.php';

/**
 * ValidationTextBox dijit
 *
 * @uses       Zend_Dojo_Form_Element_TextBox
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ValidationTextBox.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Dojo_Form_Element_ValidationTextBox extends Zend_Dojo_Form_Element_TextBox
{
    /**
     * Use ValidationTextBox dijit view helper
     * @var string
     */
    public $helper = 'ValidationTextBox';

    /**
     * Set invalidMessage
     *
     * @param  string $message
     * @return Zend_Dojo_Form_Element_ValidationTextBox
     */
    public function setInvalidMessage($message)
    {
        $this->setDijitParam('invalidMessage', (string) $message);
        return $this;
    }

    /**
     * Retrieve invalidMessage
     *
     * @return string|null
     */
    public function getInvalidMessage()
    {
        return $this->getDijitParam('invalidMessage');
    }

    /**
     * Set promptMessage
     *
     * @param  string $message
     * @return Zend_Dojo_Form_Element_ValidationTextBox
     */
    public function setPromptMessage($message)
    {
        $this->setDijitParam('promptMessage', (string) $message);
        return $this;
    }

    /**
     * Retrieve promptMessage
     *
     * @return string|null
     */
    public function getPromptMessage()
    {
        return $this->getDijitParam('promptMessage');
    }

    /**
     * Set regExp
     *
     * @param  string $regexp
     * @return Zend_Dojo_Form_Element_ValidationTextBox
     */
    public function setRegExp($regexp)
    {
        $this->setDijitParam('regExp', (string) $regexp);
        return $this;
    }

    /**
     * Retrieve regExp
     *
     * @return string|null
     */
    public function getRegExp()
    {
        return $this->getDijitParam('regExp');
    }

    /**
     * Set an individual constraint
     *
     * @param  string $key
     * @param  mixed $value
     * @return Zend_Dojo_Form_Element_ValidationTextBox
     */
    public function setConstraint($key, $value)
    {
        $constraints = $this->getConstraints();
        $constraints[(string) $key] = $value;
        $this->setConstraints($constraints);
        return $this;
    }

    /**
     * Set validation constraints
     *
     * Refer to Dojo dijit.form.ValidationTextBox documentation for valid
     * structure.
     *
     * @param  array $constraints
     * @return Zend_Dojo_Form_Element_ValidationTextBox
     */
    public function setConstraints(array $constraints)
    {
        array_walk_recursive($constraints, array($this, '_castBoolToString'));
        $this->setDijitParam('constraints', $constraints);
        return $this;
    }

    /**
     * Is the given constraint set?
     *
     * @param  string $key
     * @return bool
     */
    public function hasConstraint($key)
    {
        $constraints = $this->getConstraints();
        return array_key_exists((string)$key, $constraints);
    }

    /**
     * Get an individual constraint
     *
     * @param  string $key
     * @return mixed
     */
    public function getConstraint($key)
    {
        $key = (string) $key;
        if (!$this->hasConstraint($key)) {
            return null;
        }
        return $this->dijitParams['constraints'][$key];
    }

    /**
     * Get constraints
     *
     * @return array
     */
    public function getConstraints()
    {
        if ($this->hasDijitParam('constraints')) {
            return $this->getDijitParam('constraints');
        }
        return array();
    }

    /**
     * Remove a single constraint
     *
     * @param  string $key
     * @return Zend_Dojo_Form_Element_ValidationTextBox
     */
    public function removeConstraint($key)
    {
        $key = (string) $key;
        if ($this->hasConstraint($key)) {
            unset($this->dijitParams['constraints'][$key]);
        }
        return $this;
    }

    /**
     * Clear all constraints
     *
     * @return Zend_Dojo_Form_Element_ValidationTextBox
     */
    public function clearConstraints()
    {
        return $this->removeDijitParam('constraints');
    }

    /**
     * Cast a boolean value to a string
     *
     * @param  mixed $item
     * @param  string $key
     * @return void
     */
    protected function _castBoolToString(&$item, $key)
    {
        if (is_bool($item)) {
            $item = ($item) ? 'true' : 'false';
        }
    }
}
