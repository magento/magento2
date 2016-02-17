<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\ErrorHandler;

class Regex extends AbstractValidator
{
    const INVALID   = 'regexInvalid';
    const NOT_MATCH = 'regexNotMatch';
    const ERROROUS  = 'regexErrorous';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID   => "Invalid type given. String, integer or float expected",
        self::NOT_MATCH => "The input does not match against pattern '%pattern%'",
        self::ERROROUS  => "There was an internal error while using the pattern '%pattern%'",
    );

    /**
     * @var array
     */
    protected $messageVariables = array(
        'pattern' => 'pattern'
    );

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $pattern;

    /**
     * Sets validator options
     *
     * @param  string|Traversable $pattern
     * @throws Exception\InvalidArgumentException On missing 'pattern' parameter
     */
    public function __construct($pattern)
    {
        if (is_string($pattern)) {
            $this->setPattern($pattern);
            parent::__construct(array());
            return;
        }

        if ($pattern instanceof Traversable) {
            $pattern = ArrayUtils::iteratorToArray($pattern);
        }

        if (!is_array($pattern)) {
            throw new Exception\InvalidArgumentException('Invalid options provided to constructor');
        }

        if (!array_key_exists('pattern', $pattern)) {
            throw new Exception\InvalidArgumentException("Missing option 'pattern'");
        }

        $this->setPattern($pattern['pattern']);
        unset($pattern['pattern']);
        parent::__construct($pattern);
    }

    /**
     * Returns the pattern option
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Sets the pattern option
     *
     * @param  string $pattern
     * @throws Exception\InvalidArgumentException if there is a fatal error in pattern matching
     * @return Regex Provides a fluent interface
     */
    public function setPattern($pattern)
    {
        ErrorHandler::start();
        $this->pattern = (string) $pattern;
        $status        = preg_match($this->pattern, "Test");
        $error         = ErrorHandler::stop();

        if (false === $status) {
            throw new Exception\InvalidArgumentException(
                "Internal error parsing the pattern '{$this->pattern}'",
                0,
                $error
            );
        }

        return $this;
    }

    /**
     * Returns true if and only if $value matches against the pattern option
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        ErrorHandler::start();
        $status = preg_match($this->pattern, $value);
        ErrorHandler::stop();
        if (false === $status) {
            $this->error(self::ERROROUS);
            return false;
        }

        if (!$status) {
            $this->error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
}
