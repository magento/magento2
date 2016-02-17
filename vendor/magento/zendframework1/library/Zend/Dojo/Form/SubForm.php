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

/** Zend_Form_SubForm */
#require_once 'Zend/Form/SubForm.php';

/**
 * Dijit-enabled SubForm
 *
 * @uses       Zend_Form_SubForm
 * @package    Zend_Dojo
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Dojo_Form_SubForm extends Zend_Form_SubForm
{
    /**
     * Has the dojo view helper path been registered?
     * @var bool
     */
    protected $_dojoViewPathRegistered = false;

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
                 ->addDecorator('HtmlTag', array('tag' => 'dl'))
                 ->addDecorator('ContentPane');
        }
    }

    /**
     * Get view
     *
     * @return Zend_View_Interface
     */
    public function getView()
    {
        $view = parent::getView();
        if (!$this->_dojoViewPathRegistered) {
            if (false === $view->getPluginLoader('helper')->getPaths('Zend_Dojo_View_Helper')) {
                $view->addHelperPath('Zend/Dojo/View/Helper', 'Zend_Dojo_View_Helper');
            }
            $this->_dojoViewPathRegistered = true;
        }
        return $view;
    }
}
