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
interface Zend_Tool_Framework_Registry_Interface
{


    /**
     * setClient()
     *
     * @param Zend_Tool_Framework_Client_Abstract $client
     * @return Zend_Tool_Framework_Registry
     */
    public function setClient(Zend_Tool_Framework_Client_Abstract $client);

    /**
     * getClient() return the client in the registry
     *
     * @return Zend_Tool_Framework_Client_Abstract
     */
    public function getClient();

    /**
     * setLoader()
     *
     * @param Zend_Tool_Framework_Loader_Abstract $loader
     * @return Zend_Tool_Framework_Registry
     */
    public function setLoader(Zend_Tool_Framework_Loader_Interface $loader);

    /**
     * getLoader()
     *
     * @return Zend_Tool_Framework_Loader_Abstract
     */
    public function getLoader();

    /**
     * setActionRepository()
     *
     * @param Zend_Tool_Framework_Action_Repository $actionRepository
     * @return Zend_Tool_Framework_Registry
     */
    public function setActionRepository(Zend_Tool_Framework_Action_Repository $actionRepository);

    /**
     * getActionRepository()
     *
     * @return Zend_Tool_Framework_Action_Repository
     */
    public function getActionRepository();

    /**
     * setProviderRepository()
     *
     * @param Zend_Tool_Framework_Provider_Repository $providerRepository
     * @return Zend_Tool_Framework_Registry
     */
    public function setProviderRepository(Zend_Tool_Framework_Provider_Repository $providerRepository);

    /**
     * getProviderRepository()
     *
     * @return Zend_Tool_Framework_Provider_Repository
     */
    public function getProviderRepository();

    /**
     * setManifestRepository()
     *
     * @param Zend_Tool_Framework_Manifest_Repository $manifestRepository
     * @return Zend_Tool_Framework_Registry
     */
    public function setManifestRepository(Zend_Tool_Framework_Manifest_Repository $manifestRepository);

    /**
     * getManifestRepository()
     *
     * @return Zend_Tool_Framework_Manifest_Repository
     */
    public function getManifestRepository();

    /**
     * setRequest()
     *
     * @param Zend_Tool_Framework_Client_Request $request
     * @return Zend_Tool_Framework_Registry
     */
    public function setRequest(Zend_Tool_Framework_Client_Request $request);

    /**
     * getRequest()
     *
     * @return Zend_Tool_Framework_Client_Request
     */
    public function getRequest();

    /**
     * setResponse()
     *
     * @param Zend_Tool_Framework_Client_Response $response
     * @return Zend_Tool_Framework_Registry
     */
    public function setResponse(Zend_Tool_Framework_Client_Response $response);

    /**
     * getResponse()
     *
     * @return Zend_Tool_Framework_Client_Response
     */
    public function getResponse();

}
