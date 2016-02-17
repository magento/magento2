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
 * Validator for counting all given files
 *
 */
class Count extends AbstractValidator
{
    /**#@+
     * @const string Error constants
     */
    const TOO_MANY = 'fileCountTooMany';
    const TOO_FEW  = 'fileCountTooFew';
    /**#@-*/

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::TOO_MANY => "Too many files, maximum '%max%' are allowed but '%count%' are given",
        self::TOO_FEW  => "Too few files, minimum '%min%' are expected but '%count%' are given",
    );

    /**
     * @var array Error message template variables
     */
    protected $messageVariables = array(
        'min'   => array('options' => 'min'),
        'max'   => array('options' => 'max'),
        'count' => 'count'
    );

    /**
     * Actual filecount
     *
     * @var int
     */
    protected $count;

    /**
     * Internal file array
     * @var array
     */
    protected $files;

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = array(
        'min' => null,  // Minimum file count, if null there is no minimum file count
        'max' => null,  // Maximum file count, if null there is no maximum file count
    );

    /**
     * Sets validator options
     *
     * Min limits the file count, when used with max=null it is the maximum file count
     * It also accepts an array with the keys 'min' and 'max'
     *
     * If $options is an integer, it will be used as maximum file count
     * As Array is accepts the following keys:
     * 'min': Minimum filecount
     * 'max': Maximum filecount
     *
     * @param  int|array|\Traversable $options Options for the adapter
     */
    public function __construct($options = null)
    {
        if (is_string($options) || is_numeric($options)) {
            $options = array('max' => $options);
        }

        if (1 < func_num_args()) {
            $options['min'] = func_get_arg(0);
            $options['max'] = func_get_arg(1);
        }

        parent::__construct($options);
    }

    /**
     * Returns the minimum file count
     *
     * @return int
     */
    public function getMin()
    {
        return $this->options['min'];
    }

    /**
     * Sets the minimum file count
     *
     * @param  int|array $min The minimum file count
     * @return Count Provides a fluent interface
     * @throws Exception\InvalidArgumentException When min is greater than max
     */
    public function setMin($min)
    {
        if (is_array($min) and isset($min['min'])) {
            $min = $min['min'];
        }

        if (!is_string($min) and !is_numeric($min)) {
            throw new Exception\InvalidArgumentException('Invalid options to validator provided');
        }

        $min = (int) $min;
        if (($this->getMax() !== null) && ($min > $this->getMax())) {
            throw new Exception\InvalidArgumentException(
                "The minimum must be less than or equal to the maximum file count, but {$min} > {$this->getMax()}"
            );
        }

        $this->options['min'] = $min;
        return $this;
    }

    /**
     * Returns the maximum file count
     *
     * @return int
     */
    public function getMax()
    {
        return $this->options['max'];
    }

    /**
     * Sets the maximum file count
     *
     * @param  int|array $max The maximum file count
     * @return Count Provides a fluent interface
     * @throws Exception\InvalidArgumentException When max is smaller than min
     */
    public function setMax($max)
    {
        if (is_array($max) and isset($max['max'])) {
            $max = $max['max'];
        }

        if (!is_string($max) and !is_numeric($max)) {
            throw new Exception\InvalidArgumentException('Invalid options to validator provided');
        }

        $max = (int) $max;
        if (($this->getMin() !== null) && ($max < $this->getMin())) {
            throw new Exception\InvalidArgumentException(
                "The maximum must be greater than or equal to the minimum file count, but {$max} < {$this->getMin()}"
            );
        }

        $this->options['max'] = $max;
        return $this;
    }

    /**
     * Adds a file for validation
     *
     * @param string|array $file
     * @return Count
     */
    public function addFile($file)
    {
        if (is_string($file)) {
            $file = array($file);
        }

        if (is_array($file)) {
            foreach ($file as $name) {
                if (!isset($this->files[$name]) && !empty($name)) {
                    $this->files[$name] = $name;
                }
            }
        }

        return $this;
    }

    /**
     * Returns true if and only if the file count of all checked files is at least min and
     * not bigger than max (when max is not null). Attention: When checking with set min you
     * must give all files with the first call, otherwise you will get a false.
     *
     * @param  string|array $value Filenames to check for count
     * @param  array        $file  File data from \Zend\File\Transfer\Transfer
     * @return bool
     */
    public function isValid($value, $file = null)
    {
        if (($file !== null) && !array_key_exists('destination', $file)) {
            $file['destination'] = dirname($value);
        }

        if (($file !== null) && array_key_exists('tmp_name', $file)) {
            $value = $file['destination'] . DIRECTORY_SEPARATOR . $file['name'];
        }

        if (($file === null) || !empty($file['tmp_name'])) {
            $this->addFile($value);
        }

        $this->count = count($this->files);
        if (($this->getMax() !== null) && ($this->count > $this->getMax())) {
            return $this->throwError($file, self::TOO_MANY);
        }

        if (($this->getMin() !== null) && ($this->count < $this->getMin())) {
            return $this->throwError($file, self::TOO_FEW);
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
