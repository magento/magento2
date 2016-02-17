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
 * Validator for the hash of given files
 */
class Hash extends AbstractValidator
{
    /**
     * @const string Error constants
     */
    const DOES_NOT_MATCH = 'fileHashDoesNotMatch';
    const NOT_DETECTED   = 'fileHashHashNotDetected';
    const NOT_FOUND      = 'fileHashNotFound';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::DOES_NOT_MATCH => "File does not match the given hashes",
        self::NOT_DETECTED   => "A hash could not be evaluated for the given file",
        self::NOT_FOUND      => "File is not readable or does not exist"
    );

    /**
     * Options for this validator
     *
     * @var string
     */
    protected $options = array(
        'algorithm' => 'crc32',
        'hash'      => null,
    );

    /**
     * Sets validator options
     *
     * @param string|array $options
     */
    public function __construct($options = null)
    {
        if (is_scalar($options) ||
            (is_array($options) && !array_key_exists('hash', $options))) {
            $options = array('hash' => $options);
        }

        if (1 < func_num_args()) {
            $options['algorithm'] = func_get_arg(1);
        }

        parent::__construct($options);
    }

    /**
     * Returns the set hash values as array, the hash as key and the algorithm the value
     *
     * @return array
     */
    public function getHash()
    {
        return $this->options['hash'];
    }

    /**
     * Sets the hash for one or multiple files
     *
     * @param  string|array $options
     * @return Hash Provides a fluent interface
     */
    public function setHash($options)
    {
        $this->options['hash'] = null;
        $this->addHash($options);

        return $this;
    }

    /**
     * Adds the hash for one or multiple files
     *
     * @param  string|array $options
     * @return Hash Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function addHash($options)
    {
        if (is_string($options)) {
            $options = array($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException("False parameter given");
        }

        $known = hash_algos();
        if (!isset($options['algorithm'])) {
            $algorithm = $this->options['algorithm'];
        } else {
            $algorithm = $options['algorithm'];
            unset($options['algorithm']);
        }

        if (!in_array($algorithm, $known)) {
            throw new Exception\InvalidArgumentException("Unknown algorithm '{$algorithm}'");
        }

        foreach ($options as $value) {
            $this->options['hash'][$value] = $algorithm;
        }

        return $this;
    }

    /**
     * Returns true if and only if the given file confirms the set hash
     *
     * @param  string|array $value File to check for hash
     * @param  array        $file  File data from \Zend\File\Transfer\Transfer (optional)
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        if (is_string($value) && is_array($file)) {
            // Legacy Zend\Transfer API support
            $filename = $file['name'];
            $file     = $file['tmp_name'];
        } elseif (is_array($value)) {
            if (!isset($value['tmp_name']) || !isset($value['name'])) {
                throw new Exception\InvalidArgumentException(
                    'Value array must be in $_FILES format'
                );
            }
            $file     = $value['tmp_name'];
            $filename = $value['name'];
        } else {
            $file     = $value;
            $filename = basename($file);
        }
        $this->setValue($filename);

        // Is file readable ?
        if (empty($file) || false === stream_resolve_include_path($file)) {
            $this->error(self::NOT_FOUND);
            return false;
        }

        $algos  = array_unique(array_values($this->getHash()));
        $hashes = array_unique(array_keys($this->getHash()));
        foreach ($algos as $algorithm) {
            $filehash = hash_file($algorithm, $file);
            if ($filehash === false) {
                $this->error(self::NOT_DETECTED);
                return false;
            }

            foreach ($hashes as $hash) {
                if ($filehash === $hash) {
                    return true;
                }
            }
        }

        $this->error(self::DOES_NOT_MATCH);
        return false;
    }
}
