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
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form */
#require_once 'Zend/Form.php';

/**
 * Dijit-enabled Form
 *
 * @uses       Zend_Form
 * @package    Zend_Dojo
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Dojo_Form extends Zend_Form
{
    /**
     * Constructor
     *
     * @param  array|Zend_Config|null $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->addPrefixPath('Zend_Dojo_Form_Decorator', 'Zend/Dojo/Form/Decorator', 'decorator')
             ->addPrefixPath('Zend_Dojo_Form_Element', 'Zend/Dojo/Form/Element', 'element')
             ->addElementPrefixPath('Zend_Dojo_Form_Decorator', 'Zend/Dojo/Form/Decorator', 'decorator')
             ->addDisplayGroupPrefixPath('Zend_Dojo_Form_Decorator', 'Zend/Dojo/Form/Decorator')
             ->setDefaultDisplayGroupClass('Zend_Dojo_Form_DisplayGroup');
        parent::__construct($options);
    }

    /**
     * Load the default decorators
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
            $this->addDecorator('FormElements')
                 ->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form_dojo'))
                 ->addDecorator('DijitForm');
        }
    }

    /**
     * Set the view object
     *
     * Ensures that the view object has the dojo view helper path set.
     *
     * @param  Zend_View_Interface $view
     * @return Zend_Dojo_Form_Element_Dijit
     */
    public function setView(Zend_View_Interface $view = null)
    {
        if (null !== $view) {
            if (false === $view->getPluginLoader('helper')->getPaths('Zend_Dojo_View_Helper')) {
                $view->addHelperPath('Zend/Dojo/View/Helper', 'Zend_Dojo_View_Helper');
            }
        }
        return parent::setView($view);
    }
}
