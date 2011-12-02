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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Layout.php 20814 2010-02-01 20:13:08Z freak $
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
#require_once 'Zend/Application/Resource/ResourceAbstract.php';


/**
 * Resource for settings layout options
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Application_Resource_Layout
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_Layout
     */
    protected $_layout;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Layout
     */
    public function init()
    {
        $this->getBootstrap()->bootstrap('FrontController');
        return $this->getLayout();
    }

    /**
     * Retrieve layout object
     *
     * @return Zend_Layout
     */
    public function getLayout()
    {
        if (null === $this->_layout) {
            $this->_layout = Zend_Layout::startMvc($this->getOptions());
        }
        return $this->_layout;
    }
}
