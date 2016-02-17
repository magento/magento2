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
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Element_Multi */
#require_once 'Zend/Form/Element/Multi.php';

/**
 * MultiCheckbox form element
 *
 * Allows specifyinc a (multi-)dimensional associative array of values to use
 * as labelled checkboxes; these will return an array of values for those
 * checkboxes selected.
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Form_Element_MultiCheckbox extends Zend_Form_Element_Multi
{
    /**
     * Use formMultiCheckbox view helper by default
     * @var string
     */
    public $helper = 'formMultiCheckbox';

    /**
     * MultiCheckbox is an array of values by default
     * @var bool
     */
    protected $_isArray = true;

    /**
     * Load default decorators
     *
     * @return Zend_Form_Element_MultiCheckbox
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        parent::loadDefaultDecorators();

        // Disable 'for' attribute
        if (false !== $decorator = $this->getDecorator('label')) {
            $decorator->setOption('disableFor', true);
        }

        return $this;
    }
}
