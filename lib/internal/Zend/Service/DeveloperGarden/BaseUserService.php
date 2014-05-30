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
 * @version    $Id: BaseUserService.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Client_ClientAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Client/ClientAbstract.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/BaseUserService/GetQuotaInformationResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_BaseUserService_ChangeQuotaPoolResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/BaseUserService/ChangeQuotaPoolResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_BaseUserService_GetAccountBalanceResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/BaseUserService/GetAccountBalanceResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_BaseUserService_AccountBalance
 */
#require_once 'Zend/Service/DeveloperGarden/BaseUserService/AccountBalance.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_BaseUserService_GetQuotaInformation
 */
#require_once 'Zend/Service/DeveloperGarden/Request/BaseUserService/GetQuotaInformation.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_BaseUserService_ChangeQuotaPool
 */
#require_once 'Zend/Service/DeveloperGarden/Request/BaseUserService/ChangeQuotaPool.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_BaseUserService_GetAccountBalance
 */
#require_once 'Zend/Service/DeveloperGarden/Request/BaseUserService/GetAccountBalance.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_BaseUserService extends Zend_Service_DeveloperGarden_Client_ClientAbstract
{
    /**
     * wsdl file
     *
     * @var string
     */
    protected $_wsdlFile = 'https://gateway.developer.telekom.com/p3gw-mod-odg-admin/services/ODGBaseUserService?wsdl';

    /**
     * wsdl file local
     *
     * @var string
     */
    protected $_wsdlFileLocal = 'Wsdl/ODGBaseUserService.wsdl';

    /**
     * Response, Request Classmapping
     *
     * @var array
     *
     */
    protected $_classMap = array(
        'getQuotaInformationResponse' =>
            'Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse',
        'changeQuotaPoolResponse' =>
            'Zend_Service_DeveloperGarden_Response_BaseUserService_ChangeQuotaPoolResponse',
        'getAccountBalanceResponse' =>
            'Zend_Service_DeveloperGarden_Response_BaseUserService_GetAccountBalanceResponse',
        'AccountBalance' =>
            'Zend_Service_DeveloperGarden_BaseUserService_AccountBalance',
    );

    /**
     * array with all QuotaModuleIds
     *
     * @var array
     */
    protected $_moduleIds = array(
        'SmsProduction'            => 'SmsProduction',
        'SmsSandbox'               => 'SmsSandbox',
        'VoiceCallProduction'      => 'VoiceButlerProduction',
        'VoiceCallSandbox'         => 'VoiceButlerSandbox',
        'ConferenceCallProduction' => 'CCSProduction',
        'ConferenceCallSandbox'    => 'CCSSandbox',
        'LocalSearchProduction'    => 'localsearchProduction',
        'LocalSearchSandbox'       => 'localsearchSandbox',
        'IPLocationProduction'     => 'IPLocationProduction',
        'IPLocationSandbox'        => 'IPLocationSandbox'
    );

    /**
     * returns an array with all possible ModuleIDs
     *
     * @return array
     */
    public function getModuleIds()
    {
        return $this->_moduleIds;
    }

    /**
     * checks the moduleId and throws exception if not valid
     *
     * @param string $moduleId
     * @throws Zend_Service_DeveloperGarden_Client_Exception
     * @return void
     */
    protected function _checkModuleId($moduleId)
    {
        if (!in_array($moduleId, $this->_moduleIds)) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception('moduleId not valid');
        }
    }

    /**
     * returns the correct module string
     *
     * @param string $module
     * @param integer $environment
     * @return string
     */
    protected function _buildModuleString($module, $environment)
    {
        $moduleString = $module;
        switch($environment) {
            case self::ENV_PRODUCTION :
                $moduleString .= 'Production';
                break;
            case self::ENV_SANDBOX :
                $moduleString .= 'Sandbox';
                break;
            default:
                #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
                throw new Zend_Service_DeveloperGarden_Client_Exception(
                    'Not a valid environment supplied.'
                );
        }

        if (!in_array($moduleString, $this->_moduleIds)) {
            #require_once 'Zend/Service/DeveloperGarden/Client/Exception.php';
            throw new Zend_Service_DeveloperGarden_Client_Exception(
                'Not a valid module name supplied.'
            );
        }

        return $moduleString;
    }

    /**
     * returns the request object with the specific moduleId
     *
     * @param string $moduleId
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse
     */
    protected function _getRequestModule($moduleId)
    {
        return new Zend_Service_DeveloperGarden_Request_BaseUserService_GetQuotaInformation(
            $moduleId
        );
    }

    /**
     * returns the request object with the specific moduleId and new quotaMax value
     *
     * @param string $moduleId
     * @param integer $quotaMax
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse
     */
    protected function _getChangeRequestModule($moduleId, $quotaMax)
    {
        return new Zend_Service_DeveloperGarden_Request_BaseUserService_ChangeQuotaPool(
            $moduleId,
            $quotaMax
        );
    }

    /**
     * returns the Quota Information for SMS Service
     *
     * @param int $environment
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse
     */
    public function getSmsQuotaInformation($environment = self::ENV_PRODUCTION)
    {
        self::checkEnvironment($environment);
        $moduleId = $this->_buildModuleString('Sms', $environment);
        $request  = $this->_getRequestModule($moduleId);
        return $this->getQuotaInformation($request);
    }

    /**
     * returns the Quota Information for VoiceCall Service
     *
     * @param int $environment
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse
     */
    public function getVoiceCallQuotaInformation($environment = self::ENV_PRODUCTION)
    {
        self::checkEnvironment($environment);
        $moduleId = $this->_buildModuleString('VoiceButler', $environment);
        $request  = $this->_getRequestModule($moduleId);
        return $this->getQuotaInformation($request);
    }

    /**
     * returns the Quota Information for SMS ConferenceCall
     *
     * @param int $environment
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse
     */
    public function getConfernceCallQuotaInformation($environment = self::ENV_PRODUCTION)
    {
        self::checkEnvironment($environment);
        $moduleId = $this->_buildModuleString('CCS', $environment);
        $request  = $this->_getRequestModule($moduleId);
        return $this->getQuotaInformation($request);
    }

    /**
     * returns the Quota Information for LocaleSearch Service
     *
     * @param int $environment
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse
     */
    public function getLocalSearchQuotaInformation($environment = self::ENV_PRODUCTION)
    {
        self::checkEnvironment($environment);
        $moduleId = $this->_buildModuleString('localsearch', $environment);
        $request  = $this->_getRequestModule($moduleId);
        return $this->getQuotaInformation($request);
    }

    /**
     * returns the Quota Information for IPLocation Service
     *
     * @param int $environment
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse
     */
    public function getIPLocationQuotaInformation($environment = self::ENV_PRODUCTION)
    {
        self::checkEnvironment($environment);
        $moduleId = $this->_buildModuleString('IPLocation', $environment);
        $request  = $this->_getRequestModule($moduleId);
        return $this->getQuotaInformation($request);
    }

    /**
     * returns the quota information
     *
     * @param Zend_Service_DeveloperGarden_Request_BaseUserService $request
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_GetQuotaInformationResponse
     */
    public function getQuotaInformation(
        Zend_Service_DeveloperGarden_Request_BaseUserService_GetQuotaInformation $request
    ) {
        $this->_checkModuleId($request->getModuleId());
        return $this->getSoapClient()
                    ->getQuotaInformation($request)
                    ->parse();
    }

    /**
     * sets new user quota for the sms service
     *
     * @param integer $quotaMax
     * @param integer $environment
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_ChangeQuotaPoolResponse
     */
    public function changeSmsQuotaPool($quotaMax = 0, $environment = self::ENV_PRODUCTION)
    {
        self::checkEnvironment($environment);
        $moduleId = $this->_buildModuleString('Sms', $environment);
        $request  = $this->_getChangeRequestModule($moduleId, $quotaMax);
        return $this->changeQuotaPool($request);
    }

    /**
     * sets new user quota for the voice call service
     *
     * @param integer $quotaMax
     * @param integer $environment
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_ChangeQuotaPoolResponse
     */
    public function changeVoiceCallQuotaPool($quotaMax = 0, $environment = self::ENV_PRODUCTION)
    {
        self::checkEnvironment($environment);
        $moduleId = $this->_buildModuleString('VoiceButler', $environment);
        $request  = $this->_getChangeRequestModule($moduleId, $quotaMax);
        return $this->changeQuotaPool($request);
    }

    /**
     * sets new user quota for the IPLocation service
     *
     * @param integer $quotaMax
     * @param integer $environment
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_ChangeQuotaPoolResponse
     */
    public function changeIPLocationQuotaPool($quotaMax = 0, $environment = self::ENV_PRODUCTION)
    {
        self::checkEnvironment($environment);
        $moduleId = $this->_buildModuleString('IPLocation', $environment);
        $request  = $this->_getChangeRequestModule($moduleId, $quotaMax);
        return $this->changeQuotaPool($request);
    }

    /**
     * sets new user quota for the Conference Call service
     *
     * @param integer $quotaMax
     * @param integer $environment
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_ChangeQuotaPoolResponse
     */
    public function changeConferenceCallQuotaPool($quotaMax = 0, $environment = self::ENV_PRODUCTION)
    {
        self::checkEnvironment($environment);
        $moduleId = $this->_buildModuleString('CCS', $environment);
        $request  = $this->_getChangeRequestModule($moduleId, $quotaMax);
        return $this->changeQuotaPool($request);
    }

    /**
     * sets new user quota for the Local Search service
     *
     * @param integer $quotaMax
     * @param integer $environment
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_ChangeQuotaPoolResponse
     */
    public function changeLocalSearchQuotaPool($quotaMax = 0, $environment = self::ENV_PRODUCTION)
    {
        self::checkEnvironment($environment);
        $moduleId = $this->_buildModuleString('localsearch', $environment);
        $request  = $this->_getChangeRequestModule($moduleId, $quotaMax);
        return $this->changeQuotaPool($request);
    }

    /**
     * set new quota values for the defined module
     *
     * @param Zend_Service_DeveloperGarden_Request_BaseUserService_ChangeQuotaPool $request
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_ChangeQuotaPoolResponse
     */
    public function changeQuotaPool(
        Zend_Service_DeveloperGarden_Request_BaseUserService_ChangeQuotaPool $request
    ) {
        $this->_checkModuleId($request->getModuleId());
        return $this->getSoapClient()
                    ->changeQuotaPool($request)
                    ->parse();
    }

    /**
     * get the result for a list of accounts
     *
     * @param array $accounts
     * @return Zend_Service_DeveloperGarden_Response_BaseUserService_GetAccountBalanceResponse
     */
    public function getAccountBalance(array $accounts = array())
    {
        $request = new Zend_Service_DeveloperGarden_Request_BaseUserService_GetAccountBalance(
            $accounts
        );
        return $this->getSoapClient()
                    ->getAccountBalance($request)
                    ->parse();
    }
}
