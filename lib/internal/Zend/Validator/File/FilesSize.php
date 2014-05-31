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

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\ErrorHandler;
use Zend\Validator\Exception;

/**
 * Validator for the size of all files which will be validated in sum
 *
 * @category  Zend
 * @package   Zend_Validate
 */
class FilesSize extends Size
{
    /**
     * @const string Error constants
     */
    const TOO_BIG      = 'fileFilesSizeTooBig';
    const TOO_SMALL    = 'fileFilesSizeTooSmall';
    const NOT_READABLE = 'fileFilesSizeNotReadable';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::TOO_BIG      => "All files in sum should have a maximum size of '%max%' but '%size%' were detected",
        self::TOO_SMALL    => "All files in sum should have a minimum size of '%min%' but '%size%' were detected",
        self::NOT_READABLE => "One or more files can not be read",
    );

    /**
     * Internal file array
     *
     * @var array
     */
    protected $files;

    /**
     * Sets validator options
     *
     * Min limits the used disk space for all files, when used with max=null it is the maximum file size
     * It also accepts an array with the keys 'min' and 'max'
     *
     * @param  integer|array|Traversable $options Options for this validator
     * @throws \Zend\Validator\Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        $this->files = array();
        $this->setSize(0);

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (is_scalar($options)) {
            $options = array('max' => $options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException('Invalid options to validator provided');
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
     * Returns true if and only if the disk usage of all files is at least min and
     * not bigger than max (when max is not null).
     *
     * @param  string|array $value Real file to check for size
     * @param  array        $file  File data from \Zend\File\Transfer\Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        if (is_string($value)) {
            $value = array($value);
        }

        $min  = $this->getMin(true);
        $max  = $this->getMax(true);
        $size = $this->getSize();
        foreach ($value as $files) {
            // Is file readable ?
            if (false === stream_resolve_include_path($files)) {
                $this->throwError($file, self::NOT_READABLE);
                continue;
            }

            if (!isset($this->files[$files])) {
                $this->files[$files] = $files;
            } else {
                // file already counted... do not count twice
                continue;
            }

            // limited to 2GB files
            ErrorHandler::start();
            $size += filesize($files);
            ErrorHandler::stop();
            $this->size = $size;
            if (($max !== null) && ($max < $size)) {
                if ($this->getByteString()) {
                    $this->options['max'] = $this->toByteString($max);
                    $this->size          = $this->toByteString($size);
                    $this->throwError($file, self::TOO_BIG);
                    $this->options['max'] = $max;
                    $this->size          = $size;
                } else {
                    $this->throwError($file, self::TOO_BIG);
                }
            }
        }

        // Check that aggregate files are >= minimum size
        if (($min !== null) && ($size < $min)) {
            if ($this->getByteString()) {
                $this->options['min'] = $this->toByteString($min);
                $this->size          = $this->toByteString($size);
                $this->throwError($file, self::TOO_SMALL);
                $this->options['min'] = $min;
                $this->size          = $size;
            } else {
                $this->throwError($file, self::TOO_SMALL);
            }
        }

        if (count($this->getMessages()) > 0) {
            return false;
        }

        return true;
    }
}
