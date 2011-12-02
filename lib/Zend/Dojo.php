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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Enable Dojo components
 *
 * @package    Zend_Dojo
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Dojo.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Dojo
{
    /**
     * Base path to AOL CDN
     */
    const CDN_BASE_AOL = 'http://o.aolcdn.com/dojo/';

    /**
     * Path to dojo on AOL CDN (following version string)
     */
    const CDN_DOJO_PATH_AOL = '/dojo/dojo.xd.js';

    /**
     * Base path to Google CDN
     */
    const CDN_BASE_GOOGLE = 'http://ajax.googleapis.com/ajax/libs/dojo/';

    /**
     * Path to dojo on Google CDN (following version string)
     */
    const CDN_DOJO_PATH_GOOGLE = '/dojo/dojo.xd.js';

    /**
     * Dojo-enable a form instance
     *
     * @param  Zend_Form $form
     * @return void
     */
    public static function enableForm(Zend_Form $form)
    {
        $form->addPrefixPath('Zend_Dojo_Form_Decorator', 'Zend/Dojo/Form/Decorator', 'decorator')
             ->addPrefixPath('Zend_Dojo_Form_Element', 'Zend/Dojo/Form/Element', 'element')
             ->addElementPrefixPath('Zend_Dojo_Form_Decorator', 'Zend/Dojo/Form/Decorator', 'decorator')
             ->addDisplayGroupPrefixPath('Zend_Dojo_Form_Decorator', 'Zend/Dojo/Form/Decorator')
             ->setDefaultDisplayGroupClass('Zend_Dojo_Form_DisplayGroup');

        foreach ($form->getSubForms() as $subForm) {
            self::enableForm($subForm);
        }

        if (null !== ($view = $form->getView())) {
            self::enableView($view);
        }
    }

    /**
     * Dojo-enable a view instance
     *
     * @param  Zend_View_Interface $view
     * @return void
     */
    public static function enableView(Zend_View_Interface $view)
    {
        if (false === $view->getPluginLoader('helper')->getPaths('Zend_Dojo_View_Helper')) {
            $view->addHelperPath('Zend/Dojo/View/Helper', 'Zend_Dojo_View_Helper');
        }
    }
}

