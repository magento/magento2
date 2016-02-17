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

class IsInstanceOf extends AbstractValidator
{
    const NOT_INSTANCE_OF = 'notInstanceOf';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_INSTANCE_OF => "The input is not an instance of '%className%'",
    );

    /**
     * Additional variables available for validation failure messages
     *
     * @var array
     */
    protected $messageVariables = array(
        'className' => 'className'
    );

    /**
     * Class name
     *
     * @var string
     */
    protected $className;

    /**
     * Sets validator options
     *
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        }

        // If argument is not an array, consider first argument as class name
        if (!is_array($options)) {
            $options = func_get_args();

            $tmpOptions = array();
            $tmpOptions['className'] = array_shift($options);

            $options = $tmpOptions;
        }

        if (!array_key_exists('className', $options)) {
            throw new Exception\InvalidArgumentException('Missing option "className"');
        }

        parent::__construct($options);
    }

    /**
     * Get class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set class name
     *
     * @param  string $className
     * @return self
     */
    public function setClassName($className)
    {
        $this->className = $className;
        return $this;
    }

    /**
     * Returns true if $value is instance of $this->className
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if ($value instanceof $this->className) {
            return true;
        }
        $this->error(self::NOT_INSTANCE_OF);
        return false;
    }
}
