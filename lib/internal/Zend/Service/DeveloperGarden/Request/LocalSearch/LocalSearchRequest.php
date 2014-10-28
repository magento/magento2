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
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: LocalSearchRequest.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_RequestAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Request/RequestAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_Request_LocalSearch_LocalSearchRequest
    extends Zend_Service_DeveloperGarden_Request_RequestAbstract
{
    /**
     * array of search parameters
     *
     * @var array
     */
    public $searchParameters = null;

    /**
     * original object
     *
     * @var Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    private $_searchParameters = null;

    /**
     * account id
     *
     * @var integer
     */
    public $account = null;

    /**
     * constructor give them the environment and the sessionId
     *
     * @param integer $environment
     * @param Zend_Service_DeveloperGarden_LocalSearch_SearchParameters $searchParameters
     * @param integer $account
     * @return Zend_Service_DeveloperGarden_Request_RequestAbstract
     */
    public function __construct($environment, 
        Zend_Service_DeveloperGarden_LocalSearch_SearchParameters $searchParameters, 
        $account = null
    ) {
        parent::__construct($environment);
        $this->setSearchParameters($searchParameters)
             ->setAccount($account);
    }

    /**
     * @param integer $account
     */
    public function setAccount($account = null)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @return integer
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Zend_Service_DeveloperGarden_LocalSearch_SearchParameters $searchParameters
     */
    public function setSearchParameters(
        Zend_Service_DeveloperGarden_LocalSearch_SearchParameters $searchParameters
    ) {
        $this->searchParameters  = $searchParameters->getSearchParameters();
        $this->_searchParameters = $searchParameters;
        return $this;
    }

    /**
     * @return Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
     */
    public function getSearchParameters()
    {
        return $this->_searchParameters;
    }

}
