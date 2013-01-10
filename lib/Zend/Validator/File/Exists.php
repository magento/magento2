<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Validator
 */

namespace Zend\Validator\File;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

/**
 * Validator which checks if the file already exists in the directory
 *
 * @category  Zend
 * @package   Zend_Validate
 */
class Exists extends AbstractValidator
{
    /**
     * @const string Error constants
     */
    const DOES_NOT_EXIST = 'fileExistsDoesNotExist';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::DOES_NOT_EXIST => "File '%value%' does not exist",
    );

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = array(
        'directory' => null,  // internal list of directories
    );

    /**
     * @var array Error message template variables
     */
    protected $messageVariables = array(
        'directory' => array('options' => 'directory'),
    );

    /**
     * Sets validator options
     *
     * @param  string|array|\Traversable $options
     */
    public function __construct($options = null)
    {
        if (is_string($options)) {
            $options = explode(',', $options);
        }

        if (is_array($options) && !array_key_exists('directory', $options)) {
            $options = array('directory' => $options);
        }

        parent::__construct($options);
    }

    /**
     * Returns the set file directories which are checked
     *
     * @param  boolean $asArray Returns the values as array, when false an concatenated string is returned
     * @return string
     */
    public function getDirectory($asArray = false)
    {
        $asArray   = (bool) $asArray;
        $directory = (string) $this->options['directory'];
        if ($asArray) {
            $directory = explode(',', $directory);
        }

        return $directory;
    }

    /**
     * Sets the file directory which will be checked
     *
     * @param  string|array $directory The directories to validate
     * @return Extension Provides a fluent interface
     */
    public function setDirectory($directory)
    {
        $this->options['directory'] = null;
        $this->addDirectory($directory);
        return $this;
    }

    /**
     * Adds the file directory which will be checked
     *
     * @param  string|array $directory The directory to add for validation
     * @return Extension Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function addDirectory($directory)
    {
        $directories = $this->getDirectory(true);

        if (is_string($directory)) {
            $directory = explode(',', $directory);
        } elseif (!is_array($directory)) {
            throw new Exception\InvalidArgumentException('Invalid options to validator provided');
        }

        foreach ($directory as $content) {
            if (empty($content) || !is_string($content)) {
                continue;
            }

            $directories[] = trim($content);
        }
        $directories = array_unique($directories);

        // Sanity check to ensure no empty values
        foreach ($directories as $key => $dir) {
            if (empty($dir)) {
                unset($directories[$key]);
            }
        }

        $this->options['directory'] = implode(',', $directories);

        return $this;
    }

    /**
     * Returns true if and only if the file already exists in the set directories
     *
     * @param  string  $value Real file to check for existence
     * @param  array   $file  File data from \Zend\File\Transfer\Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        $directories = $this->getDirectory(true);
        if (($file !== null) and (!empty($file['destination']))) {
            $directories[] = $file['destination'];
        } elseif (!isset($file['name'])) {
            $file['name'] = $value;
        }

        $check = false;
        foreach ($directories as $directory) {
            if (empty($directory)) {
                continue;
            }

            $check = true;
            if (!file_exists($directory . DIRECTORY_SEPARATOR . $file['name'])) {
                return $this->throwError($file, self::DOES_NOT_EXIST);
            }
        }

        if (!$check) {
            return $this->throwError($file, self::DOES_NOT_EXIST);
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
                    $this->value = basename($file['name']);
                }
            } elseif (is_string($file)) {
                $this->value = basename($file);
            }
        }

        $this->error($errorType);
        return false;
    }
}
