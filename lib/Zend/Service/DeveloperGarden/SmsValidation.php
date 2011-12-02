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
 * @version    $Id: SmsValidation.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @see Zend_Service_DeveloperGarden_Client_ClientAbstract
 */
#require_once 'Zend/Service/DeveloperGarden/Client/ClientAbstract.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_SmsValidation_GetValidatedNumbers
 */
#require_once 'Zend/Service/DeveloperGarden/Request/SmsValidation/GetValidatedNumbers.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_SmsValidation_GetValidatedNumbersResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/SmsValidation/GetValidatedNumbersResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_SmsValidation_ValidatedNumber
 */
#require_once 'Zend/Service/DeveloperGarden/Response/SmsValidation/ValidatedNumber.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_SmsValidation_SendValidationKeyword
 */
#require_once 'Zend/Service/DeveloperGarden/Request/SmsValidation/SendValidationKeyword.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_SmsValidation_SendValidationKeywordResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/SmsValidation/SendValidationKeywordResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_SmsValidation_Validate
 */
#require_once 'Zend/Service/DeveloperGarden/Request/SmsValidation/Validate.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_SmsValidation_ValidateResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/SmsValidation/ValidateResponse.php';

/**
 * @see Zend_Service_DeveloperGarden_Request_SmsValidation_Invalidate
 */
#require_once 'Zend/Service/DeveloperGarden/Request/SmsValidation/Invalidate.php';

/**
 * @see Zend_Service_DeveloperGarden_Response_SmsValidation_InvalidateResponse
 */
#require_once 'Zend/Service/DeveloperGarden/Response/SmsValidation/InvalidateResponse.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_SmsValidation extends Zend_Service_DeveloperGarden_Client_ClientAbstract
{
// @codeCoverageIgnoreStart
    /**
     * wsdl file
     *
     * @var string
     */
    protected $_wsdlFile = 'https://gateway.developer.telekom.com/p3gw-mod-odg-sms-validation/services/SmsValidationUserService?wsdl';

    /**
     * wsdl file local
     *
     * @var string
     */
    protected $_wsdlFileLocal = 'Wsdl/SmsValidationUserService.wsdl';

    /**
     * Response, Request Classmapping
     *
     * @var array
     *
     */
    protected $_classMap = array(
        'getValidatedNumbersResponse'   => 'Zend_Service_DeveloperGarden_Response_SmsValidation_GetValidatedNumbersResponse',
        'ValidatedNumber'               => 'Zend_Service_DeveloperGarden_Response_SmsValidation_ValidatedNumber',
        'sendValidationKeywordResponse' => 'Zend_Service_DeveloperGarden_Response_SmsValidation_SendValidationKeywordResponse',
        'validateResponse'              => 'Zend_Service_DeveloperGarden_Response_SmsValidation_ValidateResponse',
        'invalidateResponse'            => 'Zend_Service_DeveloperGarden_Response_SmsValidation_InvalidateResponse',
    );

    /**
     * validate the given number with the keyword
     *
     * @param string $keyword
     * @param string $number
     * @return Zend_Service_DeveloperGarden_Response_SmsValidation_ValidateResponse
     */
    public function validate($keyword = null, $number = null)
    {
        $request = new Zend_Service_DeveloperGarden_Request_SmsValidation_Validate(
            $this->getEnvironment(),
            $keyword,
            $number
        );

        return $this->getSoapClient()
                    ->validate($request)
                    ->parse();
    }

    /**
     * invalidate the given number
     *
     * @param string $number
     * @return Zend_Service_DeveloperGarden_Response_SmsValidation_InvalidateResponse
     */
    public function inValidate($number = null)
    {
        $request = new Zend_Service_DeveloperGarden_Request_SmsValidation_Invalidate(
            $this->getEnvironment(),
            $number
        );

        return $this->getSoapClient()
                    ->invalidate($request)
                    ->parse();
    }

    /**
     * this function sends the validation sms to the given number,
     * if message is provided it should have to placeholder:
     * #key# = the validation key
     * #validUntil# = the valid until date
     *
     * @param string $number
     * @param string $message
     * @param string $originator
     * @param integer $account
     *
     * @return Zend_Service_DeveloperGarden_Request_SmsValidation_SendValidationKeywordResponse
     */
    public function sendValidationKeyword($number = null, $message = null, $originator = null, $account = null)
    {
        $request = new Zend_Service_DeveloperGarden_Request_SmsValidation_SendValidationKeyword(
            $this->getEnvironment()
        );
        $request->setNumber($number)
                ->setMessage($message)
                ->setOriginator($originator)
                ->setAccount($account);

        return $this->getSoapClient()
                    ->sendValidationKeyword($request)
                    ->parse();
    }

    /**
     * returns a list of validated numbers
     *
     * @return Zend_Service_DeveloperGarden_Response_SmsValidation_GetValidatedNumbersResponse
     */
    public function getValidatedNumbers()
    {
        $request = new Zend_Service_DeveloperGarden_Request_SmsValidation_GetValidatedNumbers(
            $this->getEnvironment()
        );
        return $this->getSoapClient()
                    ->getValidatedNumbers($request)
                    ->parse();
    }
// @codeCoverageIgnoreEnd
}
