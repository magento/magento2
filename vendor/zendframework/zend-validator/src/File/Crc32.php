<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\File;

use Zend\Validator\Exception;

/**
 * Validator for the crc32 hash of given files
 */
class Crc32 extends Hash
{
    /**
     * @const string Error constants
     */
    const DOES_NOT_MATCH = 'fileCrc32DoesNotMatch';
    const NOT_DETECTED   = 'fileCrc32NotDetected';
    const NOT_FOUND      = 'fileCrc32NotFound';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::DOES_NOT_MATCH => "File does not match the given crc32 hashes",
        self::NOT_DETECTED   => "A crc32 hash could not be evaluated for the given file",
        self::NOT_FOUND      => "File is not readable or does not exist",
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
     * Returns all set crc32 hashes
     *
     * @return array
     */
    public function getCrc32()
    {
        return $this->getHash();
    }

    /**
     * Sets the crc32 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Crc32 Provides a fluent interface
     */
    public function setCrc32($options)
    {
        $this->setHash($options);
        return $this;
    }

    /**
     * Adds the crc32 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Crc32 Provides a fluent interface
     */
    public function addCrc32($options)
    {
        $this->addHash($options);
        return $this;
    }

    /**
     * Returns true if and only if the given file confirms the set hash
     *
     * @param  string|array $value Filename to check for hash
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

        $hashes = array_unique(array_keys($this->getHash()));
        $filehash = hash_file('crc32', $file);
        if ($filehash === false) {
            $this->error(self::NOT_DETECTED);
            return false;
        }

        foreach ($hashes as $hash) {
            if ($filehash === $hash) {
                return true;
            }
        }

        $this->error(self::DOES_NOT_MATCH);
        return false;
    }
}
