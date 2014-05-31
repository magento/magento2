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
 * @version    $Id: LocalSearch.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Client_ClientAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Client/ClientAbstract.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_LocalSearch_LocalSearchResponseType
 */
#require_once 'Zend/Service/DeveloperGarden/Response/LocalSearch/LocalSearchResponseType.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_LocalSearch_LocalSearchRequest
 */
#require_once 'Zend/Service/DeveloperGarden/Request/LocalSearch/LocalSearchRequest.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_LocalSearch_LocalSearchResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/LocalSearch/LocalSearchResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_LocalSearch_SearchParameters
 */
#require_once 'Zend/Service/DeveloperGarden/LocalSearch/SearchParameters.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_LocalSearch
    extends Zend_Service_DeveloperGarden_Client_ClientAbstract
{
    /**
     * wsdl file
     *
     * @var string
     */
    protected $_wsdlFile = 'https://gateway.developer.telekom.com/p3gw-mod-odg-localsearch/services/localsearch?wsdl';

    /**
     * wsdl file local
     *
     * @var string
     */
    protected $_wsdlFileLocal = 'Wsdl/localsearch.wsdl';

    /**
     * Response, Request Classmapping
     *
     * @var array
     *
     */
    protected $_classMap = array(
        'LocalSearchResponseType' => 'Zend_Service_DeveloperGarden_Response_LocalSearch_LocalSearchResponseType'
    );

    /**
     * localSearch with the given parameters
     *
     * @param Zend_Service_DeveloperGarden_LocalSearch_SearchParameters $searchParameters
     * @param integer $account
     * @return Zend_Service_DeveloperGarden_Response_LocalSearch_LocalSearchResponseType
     */
    public function localSearch(
        Zend_Service_DeveloperGarden_LocalSearch_SearchParameters $searchParameters,
        $account = null
    ) {
        $request = new Zend_Service_DeveloperGarden_Request_LocalSearch_LocalSearchRequest(
            $this->getEnvironment(),
            $searchParameters,
            $account
        );

        $result = $this->getSoapClient()->localSearch($request);

        $response = new Zend_Service_DeveloperGarden_Response_LocalSearch_LocalSearchResponse($result);
        return $response->parse();
    }
}
