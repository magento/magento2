<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\File;

use Zend\Stdlib\ErrorHandler;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

/**
 * Validator for the maximum size of a file up to a max of 2GB
 */
class Size extends AbstractValidator
{
    /**
     * @const string Error constants
     */
    const TOO_BIG   = 'fileSizeTooBig';
    const TOO_SMALL = 'fileSizeTooSmall';
    const NOT_FOUND = 'fileSizeNotFound';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::TOO_BIG   => "Maximum allowed size for file is '%max%' but '%size%' detected",
        self::TOO_SMALL => "Minimum expected size for file is '%min%' but '%size%' detected",
        self::NOT_FOUND => "File is not readable or does not exist",
    );

    /**
     * @var array Error message template variables
     */
    protected $messageVariables = array(
        'min'  => array('options' => 'min'),
        'max'  => array('options' => 'max'),
        'size' => 'size',
    );

    /**
     * Detected size
     *
     * @var int
     */
    protected $size;

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = array(
        'min'           => null, // Minimum file size, if null there is no minimum
        'max'           => null, // Maximum file size, if null there is no maximum
        'useByteString' => true, // Use byte string?
    );

    /**
     * Sets validator options
     *
     * If $options is an integer, it will be used as maximum file size
     * As Array is accepts the following keys:
     * 'min': Minimum file size
     * 'max': Maximum file size
     * 'useByteString': Use bytestring or real size for messages
     *
     * @param  int|array|\Traversable $options Options for the adapter
     */
    public function __construct($options = null)
    {
        if (is_string($options) || is_numeric($options)) {
            $options = array('max' => $options);
        }

        if (1 < func_num_args()) {
            $argv = func_get_args();
            array_shift($argv);
            $options['max'] = array_shift($argv);
            if (!empty($argv)) {
                $options['useByteString'] = array_shift($argv);
            }
        }

        parent::__construct($options);
    }

    /**
     * Should messages return bytes as integer or as string in SI notation
     *
     * @param  bool $byteString Use bytestring ?
     * @return int
     */
    public function useByteString($byteString = true)
    {
        $this->options['useByteString'] = (bool) $byteString;
        return $this;
    }

    /**
     * Will bytestring be used?
     *
     * @return bool
     */
    public function getByteString()
    {
        return $this->options['useByteString'];
    }

    /**
     * Returns the minimum file size
     *
     * @param  bool $raw Whether or not to force return of the raw value (defaults off)
     * @return int|string
     */
    public function getMin($raw = false)
    {
        $min = $this->options['min'];
        if (!$raw && $this->getByteString()) {
            $min = $this->toByteString($min);
        }

        return $min;
    }

    /**
     * Sets the minimum file size
     *
     * File size can be an integer or a byte string
     * This includes 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'
     * For example: 2000, 2MB, 0.2GB
     *
     * @param  int|string $min The minimum file size
     * @return Size Provides a fluent interface
     * @throws Exception\InvalidArgumentException When min is greater than max
     */
    public function setMin($min)
    {
        if (!is_string($min) and !is_numeric($min)) {
            throw new Exception\InvalidArgumentException('Invalid options to validator provided');
        }

        $min = (int) $this->fromByteString($min);
        $max = $this->getMax(true);
        if (($max !== null) && ($min > $max)) {
            throw new Exception\InvalidArgumentException(
                "The minimum must be less than or equal to the maximum file size, but $min > $max"
            );
        }

        $this->options['min'] = $min;
        return $this;
    }

    /**
     * Returns the maximum file size
     *
     * @param  bool $raw Whether or not to force return of the raw value (defaults off)
     * @return int|string
     */
    public function getMax($raw = false)
    {
        $max = $this->options['max'];
        if (!$raw && $this->getByteString()) {
            $max = $this->toByteString($max);
        }

        return $max;
    }

    /**
     * Sets the maximum file size
     *
     * File size can be an integer or a byte string
     * This includes 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'
     * For example: 2000, 2MB, 0.2GB
     *
     * @param  int|string $max The maximum file size
     * @return Size Provides a fluent interface
     * @throws Exception\InvalidArgumentException When max is smaller than min
     */
    public function setMax($max)
    {
        if (!is_string($max) && !is_numeric($max)) {
            throw new Exception\InvalidArgumentException('Invalid options to validator provided');
        }

        $max = (int) $this->fromByteString($max);
        $min = $this->getMin(true);
        if (($min !== null) && ($max < $min)) {
            throw new Exception\InvalidArgumentException(
                "The maximum must be greater than or equal to the minimum file size, but $max < $min"
            );
        }

        $this->options['max'] = $max;
        return $this;
    }

    /**
     * Retrieve current detected file size
     *
     * @return int
     */
    protected function getSize()
    {
        return $this->size;
    }

    /**
     * Set current size
     *
     * @param  int $size
     * @return Size
     */
    protected function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Returns true if and only if the file size of $value is at least min and
     * not bigger than max (when max is not null).
     *
     * @param  string|array $value File to check for size
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

        // limited to 4GB files
        ErrorHandler::start();
        $size = sprintf("%u", filesize($file));
        ErrorHandler::stop();
        $this->size = $size;

        // Check to see if it's smaller than min size
        $min = $this->getMin(true);
        $max = $this->getMax(true);
        if (($min !== null) && ($size < $min)) {
            if ($this->getByteString()) {
                $this->options['min'] = $this->toByteString($min);
                $this->size          = $this->toByteString($size);
                $this->error(self::TOO_SMALL);
                $this->options['min'] = $min;
                $this->size          = $size;
            } else {
                $this->error(self::TOO_SMALL);
            }
        }

        // Check to see if it's larger than max size
        if (($max !== null) && ($max < $size)) {
            if ($this->getByteString()) {
                $this->options['max'] = $this->toByteString($max);
                $this->size          = $this->toByteString($size);
                $this->error(self::TOO_BIG);
                $this->options['max'] = $max;
                $this->size          = $size;
            } else {
                $this->error(self::TOO_BIG);
            }
        }

        if (count($this->getMessages()) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Returns the formatted size
     *
     * @param  int $size
     * @return string
     */
    protected function toByteString($size)
    {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i=0; $size >= 1024 && $i < 9; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . $sizes[$i];
    }

    /**
     * Returns the unformatted size
     *
     * @param  string $size
     * @return int
     */
    protected function fromByteString($size)
    {
        if (is_numeric($size)) {
            return (int) $size;
        }

        $type  = trim(substr($size, -2, 1));

        $value = substr($size, 0, -1);
        if (!is_numeric($value)) {
            $value = substr($value, 0, -1);
        }

        switch (strtoupper($type)) {
            case 'Y':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'Z':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'E':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'P':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'T':
                $value *= (1024 * 1024 * 1024 * 1024);
                break;
            case 'G':
                $value *= (1024 * 1024 * 1024);
                break;
            case 'M':
                $value *= (1024 * 1024);
                break;
            case 'K':
                $value *= 1024;
                break;
            default:
                break;
        }

        return $value;
    }
}
