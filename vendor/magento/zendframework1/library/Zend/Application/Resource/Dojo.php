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
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
#require_once 'Zend/Application/Resource/ResourceAbstract.php';


/**
 * Resource for settings Dojo options
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Application_Resource_Dojo
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_Dojo_View_Helper_Dojo_Container
     */
    protected $_dojo;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function init()
    {
        return $this->getDojo();
    }

    /**
     * Retrieve Dojo View Helper
     *
     * @return Zend_Dojo_View_Dojo_Container
     */
    public function getDojo()
    {
        if (null === $this->_dojo) {
            $this->getBootstrap()->bootstrap('view');
            $view = $this->getBootstrap()->view;

            Zend_Dojo::enableView($view);
            $view->dojo()->setOptions($this->getOptions());

            $this->_dojo = $view->dojo();
        }

        return $this->_dojo;
    }
}
