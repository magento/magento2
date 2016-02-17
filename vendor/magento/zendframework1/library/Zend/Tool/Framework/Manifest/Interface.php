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
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Tool_Framework_Manifest_Interface
{

    /**
     * The following methods are completely optional, and any combination of them
     * can be used as part of a manifest.  The manifest repository will process
     * the return values of these actions as specfied in the following method docblocks.
     *
     * Since these actions are
     *
     */

    /**
     * getMetadata()
     *
     * Should either return a single metadata object or an array
     * of metadata objects
     *
     * @return array|Zend_Tool_Framework_Manifest_Metadata
     **

    public function getMetadata();

     **/



    /**
     * getActions()
     *
     * Should either return a single action, or an array
     * of actions
     *
     * @return array|Zend_Tool_Framework_Action_Interface
     **

    public function getActions();

     **/



    /**
     * getProviders()
     *
     * Should either return a single provider or an array
     * of providers
     *
     **

    public function getProviders();

     **/

}
