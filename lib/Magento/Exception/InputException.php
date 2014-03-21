<?php
/**
 * Input service exception
 *
 * The top level data (code and message) is consistent across all Input Exceptions.
 * InputException is inherently build to contain aggregates.  All failure specifics are stored in params.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Exception;

class InputException extends \Magento\Exception\Exception
{
    // This will always be the value of $this->code
    const INPUT_EXCEPTION = 0;

    // These are possible code to be stored in params
    const INVALID_FIELD_RANGE = 'INVALID_FIELD_RANGE';

    const INVALID_FIELD_VALUE = 'INVALID_FIELD_VALUE';

    const REQUIRED_FIELD = 'REQUIRED_FIELD';

    /**
     * @param string $message
     * @param int    $code
     */
    public function __construct(
        $message = 'One or more input exceptions have occurred.',
        $code = self::INPUT_EXCEPTION
    ) {
        parent::__construct($message, $code);
    }

    /**
     * Create an input exception with the first error to be stored in params
     *
     * @param string $code
     * @param string $fieldName
     * @param string $value
     * @param array  $params
     * @return InputException
     */
    public static function create($code, $fieldName, $value, array $params = array())
    {
        $exception = new self();
        $exception->addError($code, $fieldName, $value, $params);
        return $exception;
    }

    /**
     * @param array $error a map of string keys to mixed values.
     * @return string
     */
    public static function translateError($error)
    {
        switch ($error['code']) {
            case InputException::INVALID_FIELD_VALUE:
            case InputException::INVALID_FIELD_RANGE:
                $message = __('Invalid value of "%1" provided for %2 field.', $error['value'], $error['fieldName']);
                break;
            case InputException::REQUIRED_FIELD:
                $message = __('%1 is a required field.', $error['fieldName']);
                break;
            default:
                $message = __('Unknown Error.');
                break;
        }
        return $message;
    }

    /**
     * Add another error to the parameters list of errors
     *
     * @param string $code      Error code
     * @param string $fieldName Fieldname with bad input
     * @param string $value     Bad input value
     * @param array  $errorData Extra error debug data
     * @return $this
     */
    public function addError($code, $fieldName, $value, array $errorData = array())
    {
        $printParams = empty($errorData) ? "[]\n" : print_r($errorData, true);
        $this->message .= "\n{\n\tcode: {$code}\n\t{$fieldName}: {$value}\n\tparams: {$printParams} }\n";
        $errorData['fieldName'] = $fieldName;
        $errorData['code'] = $code;
        $errorData['value'] = $value;
        $this->_params[] = $errorData;
        return $this;
    }

    /**
     * Returns the input errors found
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->getParams();
    }
}
