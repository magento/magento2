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
 * @subpackage View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: CustomDijit.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Dojo_View_Helper_DijitContainer */
#require_once 'Zend/Dojo/View/Helper/DijitContainer.php';

/**
 * Arbitrary dijit support
 *
 * @uses       Zend_Dojo_View_Helper_DijitContainer
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
  */
class Zend_Dojo_View_Helper_CustomDijit extends Zend_Dojo_View_Helper_DijitContainer
{
    /**
     * Default dojoType; set the value when extending
     * @var string
     */
    protected $_defaultDojoType;

    /**
     * Render a custom dijit
     *
     * Requires that either the {@link $_defaultDojotype} property is set, or
     * that you pass a value to the "dojoType" key of the $params argument.
     *
     * @param  string $id
     * @param  string $value
     * @param  array $params
     * @param  array $attribs
     * @return string|Zend_Dojo_View_Helper_CustomDijit
     */
    public function customDijit($id = null, $value = null, array $params = array(), array $attribs = array())
    {
        if (null === $id) {
            return $this;
        }

        if (!array_key_exists('dojoType', $params)
            && (null === $this->_defaultDojoType)
        ) {
            #require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception('No dojoType specified; cannot create dijit');
        } elseif (array_key_exists('dojoType', $params)) {
            $this->_dijit  = $params['dojoType'];
            $this->_module = $params['dojoType'];
            unset($params['dojoType']);
        } else {
            $this->_dijit  = $this->_defaultDojoType;
            $this->_module = $this->_defaultDojoType;
        }

        if (array_key_exists('rootNode', $params)) {
            $this->setRootNode($params['rootNode']);
            unset($params['rootNode']);
        }

        return $this->_createLayoutContainer($id, $value, $params, $attribs);
    }

    /**
     * Begin capturing content.
     *
     * Requires that either the {@link $_defaultDojotype} property is set, or
     * that you pass a value to the "dojoType" key of the $params argument.
     *
     * @param  string $id
     * @param  array $params
     * @param  array $attribs
     * @return void
     */
    public function captureStart($id, array $params = array(), array $attribs = array())
    {
        if (!array_key_exists('dojoType', $params)
            && (null === $this->_defaultDojoType)
        ) {
            #require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception('No dojoType specified; cannot create dijit');
        } elseif (array_key_exists('dojoType', $params)) {
            $this->_dijit  = $params['dojoType'];
            $this->_module = $params['dojoType'];
            unset($params['dojoType']);
        } else {
            $this->_dijit  = $this->_defaultDojoType;
            $this->_module = $this->_defaultDojoType;
        }

        return parent::captureStart($id, $params, $attribs);
    }
}
