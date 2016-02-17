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

/** Zend_Form_DisplayGroup */
#require_once 'Zend/Form/DisplayGroup.php';

/**
 * Dijit-enabled DisplayGroup
 *
 * @uses       Zend_Form_DisplayGroup
 * @package    Zend_Dojo
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Dojo_Form_DisplayGroup extends Zend_Form_DisplayGroup
{
    /**
     * Constructor
     *
     * @param  string $name
     * @param  Zend_Loader_PluginLoader $loader
     * @param  array|Zend_Config|null $options
     * @return void
     */
    public function __construct($name, Zend_Loader_PluginLoader $loader, $options = null)
    {
        parent::__construct($name, $loader, $options);
        $this->addPrefixPath('Zend_Dojo_Form_Decorator', 'Zend/Dojo/Form/Decorator');
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
