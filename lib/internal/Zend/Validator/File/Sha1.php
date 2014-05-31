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

use Zend\Validator\Exception;

/**
 * Validator for the sha1 hash of given files
 *
 * @category  Zend
 * @package   Zend_Validate
 */
class Sha1 extends Hash
{
    /**
     * @const string Error constants
     */
    const DOES_NOT_MATCH = 'fileSha1DoesNotMatch';
    const NOT_DETECTED   = 'fileSha1NotDetected';
    const NOT_FOUND      = 'fileSha1NotFound';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::DOES_NOT_MATCH => "File '%value%' does not match the given sha1 hashes",
        self::NOT_DETECTED   => "A sha1 hash could not be evaluated for the given file",
        self::NOT_FOUND      => "File '%value%' is not readable or does not exist",
    );

    /**
     * Options for this validator
     *
     * @var string
     */
    protected $options = array(
        'algorithm' => 'sha1',
        'hash'      => null,
    );

    /**
     * Returns all set sha1 hashes
     *
     * @return array
     */
    public function getSha1()
    {
        return $this->getHash();
    }

    /**
     * Sets the sha1 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Hash Provides a fluent interface
     */
    public function setSha1($options)
    {
        $this->setHash($options);
        return $this;
    }

    /**
     * Adds the sha1 hash for one or multiple files
     *
     * @param  string|array $options
     * @return Hash Provides a fluent interface
     */
    public function addSha1($options)
    {
        $this->addHash($options);
        return $this;
    }

    /**
     * Returns true if and only if the given file confirms the set hash
     *
     * @param  string $value Filename to check for hash
     * @param  array  $file  File data from \Zend\File\Transfer\Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        if ($file === null) {
            $file = array('name' => basename($value));
        }

        // Is file readable ?
        if (false === stream_resolve_include_path($value)) {
            return $this->throwError($file, self::NOT_FOUND);
        }

        $hashes = array_unique(array_keys($this->getHash()));
        $filehash = hash_file('sha1', $value);
        if ($filehash === false) {
            return $this->throwError($file, self::NOT_DETECTED);
        }

        foreach ($hashes as $hash) {
            if ($filehash === $hash) {
                return true;
            }
        }

        return $this->throwError($file, self::DOES_NOT_MATCH);
    }
}
