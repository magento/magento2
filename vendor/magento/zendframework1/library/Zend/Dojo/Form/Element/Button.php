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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Dojo_Form_Element_Dijit */
#require_once 'Zend/Dojo/Form/Element/Dijit.php';

/**
 * Button dijit
 *
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Dojo_Form_Element_Button extends Zend_Dojo_Form_Element_Dijit
{
    /**
     * Use Button dijit view helper
     * @var string
     */
    public $helper = 'Button';

    /**
     * Constructor
     *
     * @param  string|array|Zend_Config $spec Element name or configuration
     * @param  string|array|Zend_Config $options Element value or configuration
     * @return void
     */
    public function __construct($spec, $options = null)
    {
        if (is_string($spec) && ((null !== $options) && is_string($options))) {
            $options = array('label' => $options);
        }

        parent::__construct($spec, $options);
    }

    /**
     * Return label
     *
     * If no label is present, returns the currently set name.
     *
     * If a translator is present, returns the translated label.
     *
     * @return string
     */
    public function getLabel()
    {
        $value = parent::getLabel();

        if (null === $value) {
            $value = $this->getName();
        }

        if (null !== ($translator = $this->getTranslator())) {
            return $translator->translate($value);
        }

        return $value;
    }

    /**
     * Has this submit button been selected?
     *
     * @return bool
     */
    public function isChecked()
    {
        $value = $this->getValue();

        if (empty($value)) {
            return false;
        }
        if ($value != $this->getLabel()) {
            return false;
        }

        return true;
    }

    /**
     * Default decorators
     *
     * Uses only 'DijitElement' and 'DtDdWrapper' decorators by default.
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('DijitElement')
                 ->addDecorator('DtDdWrapper');
        }
    }
}
