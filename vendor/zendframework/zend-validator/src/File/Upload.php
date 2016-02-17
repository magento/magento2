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
 *
 */
class Upload extends AbstractValidator
{
    /**
     * @const string Error constants
     */
    const INI_SIZE       = 'fileUploadErrorIniSize';
    const FORM_SIZE      = 'fileUploadErrorFormSize';
    const PARTIAL        = 'fileUploadErrorPartial';
    const NO_FILE        = 'fileUploadErrorNoFile';
    const NO_TMP_DIR     = 'fileUploadErrorNoTmpDir';
    const CANT_WRITE     = 'fileUploadErrorCantWrite';
    const EXTENSION      = 'fileUploadErrorExtension';
    const ATTACK         = 'fileUploadErrorAttack';
    const FILE_NOT_FOUND = 'fileUploadErrorFileNotFound';
    const UNKNOWN        = 'fileUploadErrorUnknown';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::INI_SIZE       => "File '%value%' exceeds the defined ini size",
        self::FORM_SIZE      => "File '%value%' exceeds the defined form size",
        self::PARTIAL        => "File '%value%' was only partially uploaded",
        self::NO_FILE        => "File '%value%' was not uploaded",
        self::NO_TMP_DIR     => "No temporary directory was found for file '%value%'",
        self::CANT_WRITE     => "File '%value%' can't be written",
        self::EXTENSION      => "A PHP extension returned an error while uploading the file '%value%'",
        self::ATTACK         => "File '%value%' was illegally uploaded. This could be a possible attack",
        self::FILE_NOT_FOUND => "File '%value%' was not found",
        self::UNKNOWN        => "Unknown error while uploading file '%value%'"
    );

    protected $options = array(
        'files' => array(),
    );

    /**
     * Sets validator options
     *
     * The array $files must be given in syntax of Zend\File\Transfer\Transfer to be checked
     * If no files are given the $_FILES array will be used automatically.
     * NOTE: This validator will only work with HTTP POST uploads!
     *
     * @param  array|\Traversable $options Array of files in syntax of \Zend\File\Transfer\Transfer
     */
    public function __construct($options = array())
    {
        if (is_array($options) && !array_key_exists('files', $options)) {
            $options = array('files' => $options);
        }

        parent::__construct($options);
    }

    /**
     * Returns the array of set files
     *
     * @param  string $file (Optional) The file to return in detail
     * @return array
     * @throws Exception\InvalidArgumentException If file is not found
     */
    public function getFiles($file = null)
    {
        if ($file !== null) {
            $return = array();
            foreach ($this->options['files'] as $name => $content) {
                if ($name === $file) {
                    $return[$file] = $this->options['files'][$name];
                }

                if ($content['name'] === $file) {
                    $return[$name] = $this->options['files'][$name];
                }
            }

            if (count($return) === 0) {
                throw new Exception\InvalidArgumentException("The file '$file' was not found");
            }

            return $return;
        }

        return $this->options['files'];
    }

    /**
     * Sets the files to be checked
     *
     * @param  array $files The files to check in syntax of \Zend\File\Transfer\Transfer
     * @return Upload Provides a fluent interface
     */
    public function setFiles($files = array())
    {
        if (count($files) === 0) {
            $this->options['files'] = $_FILES;
        } else {
            $this->options['files'] = $files;
        }

        if ($this->options['files'] === null) {
            $this->options['files'] = array();
        }

        foreach ($this->options['files'] as $file => $content) {
            if (!isset($content['error'])) {
                unset($this->options['files'][$file]);
            }
        }

        return $this;
    }

    /**
     * Returns true if and only if the file was uploaded without errors
     *
     * @param  string $value Single file to check for upload errors, when giving null the $_FILES array
     *                       from initialization will be used
     * @param  mixed  $file
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        $files = array();
        $this->setValue($value);
        if (array_key_exists($value, $this->getFiles())) {
            $files = array_merge($files, $this->getFiles($value));
        } else {
            foreach ($this->getFiles() as $file => $content) {
                if (isset($content['name']) && ($content['name'] === $value)) {
                    $files = array_merge($files, $this->getFiles($file));
                }

                if (isset($content['tmp_name']) && ($content['tmp_name'] === $value)) {
                    $files = array_merge($files, $this->getFiles($file));
                }
            }
        }

        if (empty($files)) {
            return $this->throwError($file, self::FILE_NOT_FOUND);
        }

        foreach ($files as $file => $content) {
            $this->value = $file;
            switch ($content['error']) {
                case 0:
                    if (!is_uploaded_file($content['tmp_name'])) {
                        $this->throwError($content, self::ATTACK);
                    }
                    break;

                case 1:
                    $this->throwError($content, self::INI_SIZE);
                    break;

                case 2:
                    $this->throwError($content, self::FORM_SIZE);
                    break;

                case 3:
                    $this->throwError($content, self::PARTIAL);
                    break;

                case 4:
                    $this->throwError($content, self::NO_FILE);
                    break;

                case 6:
                    $this->throwError($content, self::NO_TMP_DIR);
                    break;

                case 7:
                    $this->throwError($content, self::CANT_WRITE);
                    break;

                case 8:
                    $this->throwError($content, self::EXTENSION);
                    break;

                default:
                    $this->throwError($content, self::UNKNOWN);
                    break;
            }
        }

        if (count($this->getMessages()) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Throws an error of the given type
     *
     * @param  string $file
     * @param  string $errorType
     * @return false
     */
    protected function throwError($file, $errorType)
    {
        if ($file !== null) {
            if (is_array($file)) {
                if (array_key_exists('name', $file)) {
                    $this->value = $file['name'];
                }
            } elseif (is_string($file)) {
                $this->value = $file;
            }
        }

        $this->error($errorType);
        return false;
    }
}
