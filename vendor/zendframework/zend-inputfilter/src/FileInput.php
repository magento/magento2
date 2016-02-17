<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

use Zend\Validator\File\UploadFile as UploadValidator;

/**
 * FileInput is a special Input type for handling uploaded files.
 *
 * It differs from Input in a few ways:
 *
 * 1. It expects the raw value to be in the $_FILES array format.
 *
 * 2. The validators are run **before** the filters (the opposite behavior of Input).
 *    This is so is_uploaded_file() validation can be run prior to any filters that
 *    may rename/move/modify the file.
 *
 * 3. Instead of adding a NotEmpty validator, it will (by default) automatically add
 *    a Zend\Validator\File\Upload validator.
 */
class FileInput extends Input
{
    /**
     * @var bool
     */
    protected $isValid = false;

    /**
     * @var bool
     */
    protected $autoPrependUploadValidator = true;

    /**
     * @param  bool $value Enable/Disable automatically prepending an Upload validator
     * @return FileInput
     */
    public function setAutoPrependUploadValidator($value)
    {
        $this->autoPrependUploadValidator = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAutoPrependUploadValidator()
    {
        return $this->autoPrependUploadValidator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->value;
        if ($this->isValid && is_array($value)) {
            // Run filters ~after~ validation, so that is_uploaded_file()
            // validation is not affected by filters.
            $filter = $this->getFilterChain();
            if (isset($value['tmp_name'])) {
                // Single file input
                $value = $filter->filter($value);
            } else {
                // Multi file input (multiple attribute set)
                $newValue = array();
                foreach ($value as $fileData) {
                    if (is_array($fileData) && isset($fileData['tmp_name'])) {
                        $newValue[] = $filter->filter($fileData);
                    }
                }
                $value = $newValue;
            }
        }

        return $value;
    }

    /**
     * Checks if the raw input value is an empty file input eg: no file was uploaded
     *
     * @param $rawValue
     * @return bool
     */
    public function isEmptyFile($rawValue)
    {
        if (!is_array($rawValue)) {
            return true;
        }

        if (isset($rawValue['error']) && $rawValue['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        if (count($rawValue) === 1 && isset($rawValue[0])) {
            return $this->isEmptyFile($rawValue[0]);
        }

        return false;
    }

    /**
     * @param  mixed $context Extra "context" to provide the validator
     * @return bool
     */
    public function isValid($context = null)
    {
        $rawValue        = $this->getRawValue();
        $hasValue        = $this->hasValue();
        $empty           = $this->isEmptyFile($rawValue);
        $required        = $this->isRequired();
        $allowEmpty      = $this->allowEmpty();
        $continueIfEmpty = $this->continueIfEmpty();

        if (! $hasValue && ! $required) {
            return true;
        }

        if (! $hasValue && $required && ! $this->hasFallback()) {
            if ($this->errorMessage === null) {
                $this->errorMessage = $this->prepareRequiredValidationFailureMessage();
            }
            return false;
        }

        if ($empty && ! $required && ! $continueIfEmpty) {
            return true;
        }

        if ($empty && $allowEmpty && ! $continueIfEmpty) {
            return true;
        }

        $this->injectUploadValidator();
        $validator = $this->getValidatorChain();
        //$value   = $this->getValue(); // Do not run the filters yet for File uploads (see getValue())

        if (!is_array($rawValue)) {
            // This can happen in an AJAX POST, where the input comes across as a string
            $rawValue = array(
                'tmp_name' => $rawValue,
                'name'     => $rawValue,
                'size'     => 0,
                'type'     => '',
                'error'    => UPLOAD_ERR_NO_FILE,
            );
        }
        if (is_array($rawValue) && isset($rawValue['tmp_name'])) {
            // Single file input
            $this->isValid = $validator->isValid($rawValue, $context);
        } elseif (is_array($rawValue) && !empty($rawValue) && isset($rawValue[0]['tmp_name'])) {
            // Multi file input (multiple attribute set)
            $this->isValid = true;
            foreach ($rawValue as $value) {
                if (!$validator->isValid($value, $context)) {
                    $this->isValid = false;
                    break; // Do not continue processing files if validation fails
                }
            }
        }

        return $this->isValid;
    }

    /**
     * @return void
     */
    protected function injectUploadValidator()
    {
        if (!$this->autoPrependUploadValidator) {
            return;
        }
        $chain = $this->getValidatorChain();

        // Check if Upload validator is already first in chain
        $validators = $chain->getValidators();
        if (isset($validators[0]['instance'])
            && $validators[0]['instance'] instanceof UploadValidator
        ) {
            $this->autoPrependUploadValidator = false;
            return;
        }

        $chain->prependByName('fileuploadfile', array(), true);
        $this->autoPrependUploadValidator = false;
    }

    /**
     * @deprecated 2.4.8 See note on parent class. Removal does not affect this class.
     *
     * No-op, NotEmpty validator does not apply for FileInputs.
     * See also: BaseInputFilter::isValid()
     *
     * @return void
     */
    protected function injectNotEmptyValidator()
    {
        $this->notEmptyValidator = true;
    }

    /**
     * @param  InputInterface $input
     * @return FileInput
     */
    public function merge(InputInterface $input)
    {
        parent::merge($input);
        if ($input instanceof FileInput) {
            $this->setAutoPrependUploadValidator($input->getAutoPrependUploadValidator());
        }
        return $this;
    }
}
