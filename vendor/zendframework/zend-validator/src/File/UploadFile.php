<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\File;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

/**
 * Validator for the maximum size of a file up to a max of 2GB
 */
class UploadFile extends AbstractValidator
{
    /**
     * @const string Error constants
     */
    const INI_SIZE       = 'fileUploadFileErrorIniSize';
    const FORM_SIZE      = 'fileUploadFileErrorFormSize';
    const PARTIAL        = 'fileUploadFileErrorPartial';
    const NO_FILE        = 'fileUploadFileErrorNoFile';
    const NO_TMP_DIR     = 'fileUploadFileErrorNoTmpDir';
    const CANT_WRITE     = 'fileUploadFileErrorCantWrite';
    const EXTENSION      = 'fileUploadFileErrorExtension';
    const ATTACK         = 'fileUploadFileErrorAttack';
    const FILE_NOT_FOUND = 'fileUploadFileErrorFileNotFound';
    const UNKNOWN        = 'fileUploadFileErrorUnknown';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::INI_SIZE       => "File exceeds the defined ini size",
        self::FORM_SIZE      => "File exceeds the defined form size",
        self::PARTIAL        => "File was only partially uploaded",
        self::NO_FILE        => "File was not uploaded",
        self::NO_TMP_DIR     => "No temporary directory was found for file",
        self::CANT_WRITE     => "File can't be written",
        self::EXTENSION      => "A PHP extension returned an error while uploading the file",
        self::ATTACK         => "File was illegally uploaded. This could be a possible attack",
        self::FILE_NOT_FOUND => "File was not found",
        self::UNKNOWN        => "Unknown error while uploading file",
    );

    /**
     * Returns true if and only if the file was uploaded without errors
     *
     * @param  string $value File to check for upload errors
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public function isValid($value)
    {
        if (is_array($value)) {
            if (!isset($value['tmp_name']) || !isset($value['name']) || !isset($value['error'])) {
                throw new Exception\InvalidArgumentException(
                    'Value array must be in $_FILES format'
                );
            }
            $file     = $value['tmp_name'];
            $filename = $value['name'];
            $error    = $value['error'];
        } else {
            $file     = $value;
            $filename = basename($file);
            $error    = 0;
        }
        $this->setValue($filename);

        switch ($error) {
            case UPLOAD_ERR_OK:
                if (empty($file) || false === is_file($file)) {
                    $this->error(self::FILE_NOT_FOUND);
                } elseif (! is_uploaded_file($file)) {
                    $this->error(self::ATTACK);
                }
                break;

            case UPLOAD_ERR_INI_SIZE:
                $this->error(self::INI_SIZE);
                break;

            case UPLOAD_ERR_FORM_SIZE:
                $this->error(self::FORM_SIZE);
                break;

            case UPLOAD_ERR_PARTIAL:
                $this->error(self::PARTIAL);
                break;

            case UPLOAD_ERR_NO_FILE:
                $this->error(self::NO_FILE);
                break;

            case UPLOAD_ERR_NO_TMP_DIR:
                $this->error(self::NO_TMP_DIR);
                break;

            case UPLOAD_ERR_CANT_WRITE:
                $this->error(self::CANT_WRITE);
                break;

            case UPLOAD_ERR_EXTENSION:
                $this->error(self::EXTENSION);
                break;

            default:
                $this->error(self::UNKNOWN);
                break;
        }

        if (count($this->getMessages()) > 0) {
            return false;
        }

        return true;
    }
}
