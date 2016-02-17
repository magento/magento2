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

abstract class AbstractValidator implements
    Translator\TranslatorAwareInterface,
    ValidatorInterface
{
    /**
     * The value to be validated
     *
     * @var mixed
     */
    protected $value;

    /**
     * Default translation object for all validate objects
     * @var Translator\TranslatorInterface
     */
    protected static $defaultTranslator;

    /**
     * Default text domain to be used with translator
     * @var string
     */
    protected static $defaultTranslatorTextDomain = 'default';

    /**
     * Limits the maximum returned length of an error message
     *
     * @var int
     */
    protected static $messageLength = -1;

    protected $abstractOptions = array(
        'messages'             => array(), // Array of validation failure messages
        'messageTemplates'     => array(), // Array of validation failure message templates
        'messageVariables'     => array(), // Array of additional variables available for validation failure messages
        'translator'           => null,    // Translation object to used -> Translator\TranslatorInterface
        'translatorTextDomain' => null,    // Translation text domain
        'translatorEnabled'    => true,    // Is translation enabled?
        'valueObscured'        => false,   // Flag indicating whether or not value should be obfuscated
                                           // in error messages
    );

    /**
     * Abstract constructor for all validators
     * A validator should accept following parameters:
     *  - nothing f.e. Validator()
     *  - one or multiple scalar values f.e. Validator($first, $second, $third)
     *  - an array f.e. Validator(array($first => 'first', $second => 'second', $third => 'third'))
     *  - an instance of Traversable f.e. Validator($config_instance)
     *
     * @param array|Traversable $options
     */
    public function __construct($options = null)
    {
        // The abstract constructor allows no scalar values
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (isset($this->messageTemplates)) {
            $this->abstractOptions['messageTemplates'] = $this->messageTemplates;
        }

        if (isset($this->messageVariables)) {
            $this->abstractOptions['messageVariables'] = $this->messageVariables;
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Returns an option
     *
     * @param string $option Option to be returned
     * @return mixed Returned option
     * @throws Exception\InvalidArgumentException
     */
    public function getOption($option)
    {
        if (array_key_exists($option, $this->abstractOptions)) {
            return $this->abstractOptions[$option];
        }

        if (isset($this->options) && array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        throw new Exception\InvalidArgumentException("Invalid option '$option'");
    }

    /**
     * Returns all available options
     *
     * @return array Array with all available options
     */
    public function getOptions()
    {
        $result = $this->abstractOptions;
        if (isset($this->options)) {
            $result += $this->options;
        }
        return $result;
    }

    /**
     * Sets one or multiple options
     *
     * @param  array|Traversable $options Options to set
     * @throws Exception\InvalidArgumentException If $options is not an array or Traversable
     * @return AbstractValidator Provides fluid interface
     */
    public function setOptions($options = array())
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }

        foreach ($options as $name => $option) {
            $fname = 'set' . ucfirst($name);
            $fname2 = 'is' . ucfirst($name);
            if (($name != 'setOptions') && method_exists($this, $name)) {
                $this->{$name}($option);
            } elseif (($fname != 'setOptions') && method_exists($this, $fname)) {
                $this->{$fname}($option);
            } elseif (method_exists($this, $fname2)) {
                $this->{$fname2}($option);
            } elseif (isset($this->options)) {
                $this->options[$name] = $option;
            } else {
                $this->abstractOptions[$name] = $option;
            }
        }

        return $this;
    }

    /**
     * Returns array of validation failure messages
     *
     * @return array
     */
    public function getMessages()
    {
        return array_unique($this->abstractOptions['messages'], SORT_REGULAR);
    }

    /**
     * Invoke as command
     *
     * @param  mixed $value
     * @return bool
     */
    public function __invoke($value)
    {
        return $this->isValid($value);
    }

    /**
     * Returns an array of the names of variables that are used in constructing validation failure messages
     *
     * @return array
     */
    public function getMessageVariables()
    {
        return array_keys($this->abstractOptions['messageVariables']);
    }

    /**
     * Returns the message templates from the validator
     *
     * @return array
     */
    public function getMessageTemplates()
    {
        return $this->abstractOptions['messageTemplates'];
    }

    /**
     * Sets the validation failure message template for a particular key
     *
     * @param  string $messageString
     * @param  string $messageKey     OPTIONAL
     * @return AbstractValidator Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setMessage($messageString, $messageKey = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->abstractOptions['messageTemplates']);
            foreach ($keys as $key) {
                $this->setMessage($messageString, $key);
            }
            return $this;
        }

        if (!isset($this->abstractOptions['messageTemplates'][$messageKey])) {
            throw new Exception\InvalidArgumentException("No message template exists for key '$messageKey'");
        }

        $this->abstractOptions['messageTemplates'][$messageKey] = $messageString;
        return $this;
    }

    /**
     * Sets validation failure message templates given as an array, where the array keys are the message keys,
     * and the array values are the message template strings.
     *
     * @param  array $messages
     * @return AbstractValidator
     */
    public function setMessages(array $messages)
    {
        foreach ($messages as $key => $message) {
            $this->setMessage($message, $key);
        }
        return $this;
    }

    /**
     * Magic function returns the value of the requested property, if and only if it is the value or a
     * message variable.
     *
     * @param  string $property
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function __get($property)
    {
        if ($property == 'value') {
            return $this->value;
        }

        if (array_key_exists($property, $this->abstractOptions['messageVariables'])) {
            $result = $this->abstractOptions['messageVariables'][$property];
            if (is_array($result)) {
                return $this->{key($result)}[current($result)];
            }
            return $this->{$result};
        }

        if (isset($this->messageVariables) && array_key_exists($property, $this->messageVariables)) {
            $result = $this->{$this->messageVariables[$property]};
            if (is_array($result)) {
                return $this->{key($result)}[current($result)];
            }
            return $this->{$result};
        }

        throw new Exception\InvalidArgumentException("No property exists by the name '$property'");
    }

    /**
     * Constructs and returns a validation failure message with the given message key and value.
     *
     * Returns null if and only if $messageKey does not correspond to an existing template.
     *
     * If a translator is available and a translation exists for $messageKey,
     * the translation will be used.
     *
     * @param  string              $messageKey
     * @param  string|array|object $value
     * @return string
     */
    protected function createMessage($messageKey, $value)
    {
        if (!isset($this->abstractOptions['messageTemplates'][$messageKey])) {
            return;
        }

        $message = $this->abstractOptions['messageTemplates'][$messageKey];

        $message = $this->translateMessage($messageKey, $message);

        if (is_object($value) &&
            !in_array('__toString', get_class_methods($value))
        ) {
            $value = get_class($value) . ' object';
        } elseif (is_array($value)) {
            $value = var_export($value, 1);
        } else {
            $value = (string) $value;
        }

        if ($this->isValueObscured()) {
            $value = str_repeat('*', strlen($value));
        }

        $message = str_replace('%value%', (string) $value, $message);
        foreach ($this->abstractOptions['messageVariables'] as $ident => $property) {
            if (is_array($property)) {
                $value = $this->{key($property)}[current($property)];
                if (is_array($value)) {
                    $value = '[' . implode(', ', $value) . ']';
                }
            } else {
                $value = $this->$property;
            }
            $message = str_replace("%$ident%", (string) $value, $message);
        }

        $length = self::getMessageLength();
        if (($length > -1) && (strlen($message) > $length)) {
            $message = substr($message, 0, ($length - 3)) . '...';
        }

        return $message;
    }

    /**
     * @param  string $messageKey
     * @param  string $value      OPTIONAL
     * @return void
     */
    protected function error($messageKey, $value = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->abstractOptions['messageTemplates']);
            $messageKey = current($keys);
        }

        if ($value === null) {
            $value = $this->value;
        }

        $this->abstractOptions['messages'][$messageKey] = $this->createMessage($messageKey, $value);
    }

    /**
     * Returns the validation value
     *
     * @return mixed Value to be validated
     */
    protected function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value to be validated and clears the messages and errors arrays
     *
     * @param  mixed $value
     * @return void
     */
    protected function setValue($value)
    {
        $this->value               = $value;
        $this->abstractOptions['messages'] = array();
    }

    /**
     * Set flag indicating whether or not value should be obfuscated in messages
     *
     * @param  bool $flag
     * @return AbstractValidator
     */
    public function setValueObscured($flag)
    {
        $this->abstractOptions['valueObscured'] = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve flag indicating whether or not value should be obfuscated in
     * messages
     *
     * @return bool
     */
    public function isValueObscured()
    {
        return $this->abstractOptions['valueObscured'];
    }

    /**
     * Set translation object
     *
     * @param  Translator\TranslatorInterface|null $translator
     * @param  string          $textDomain (optional)
     * @return AbstractValidator
     * @throws Exception\InvalidArgumentException
     */
    public function setTranslator(Translator\TranslatorInterface $translator = null, $textDomain = null)
    {
        $this->abstractOptions['translator'] = $translator;
        if (null !== $textDomain) {
            $this->setTranslatorTextDomain($textDomain);
        }
        return $this;
    }

    /**
     * Return translation object
     *
     * @return Translator\TranslatorInterface|null
     */
    public function getTranslator()
    {
        if (! $this->isTranslatorEnabled()) {
            return;
        }

        if (null === $this->abstractOptions['translator']) {
            $this->abstractOptions['translator'] = self::getDefaultTranslator();
        }

        return $this->abstractOptions['translator'];
    }

    /**
     * Does this validator have its own specific translator?
     *
     * @return bool
     */
    public function hasTranslator()
    {
        return (bool) $this->abstractOptions['translator'];
    }

    /**
     * Set translation text domain
     *
     * @param  string $textDomain
     * @return AbstractValidator
     */
    public function setTranslatorTextDomain($textDomain = 'default')
    {
        $this->abstractOptions['translatorTextDomain'] = $textDomain;
        return $this;
    }

    /**
     * Return the translation text domain
     *
     * @return string
     */
    public function getTranslatorTextDomain()
    {
        if (null === $this->abstractOptions['translatorTextDomain']) {
            $this->abstractOptions['translatorTextDomain'] =
                self::getDefaultTranslatorTextDomain();
        }
        return $this->abstractOptions['translatorTextDomain'];
    }

    /**
     * Set default translation object for all validate objects
     *
     * @param  Translator\TranslatorInterface|null $translator
     * @param  string          $textDomain (optional)
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public static function setDefaultTranslator(Translator\TranslatorInterface $translator = null, $textDomain = null)
    {
        static::$defaultTranslator = $translator;
        if (null !== $textDomain) {
            self::setDefaultTranslatorTextDomain($textDomain);
        }
    }

    /**
     * Get default translation object for all validate objects
     *
     * @return Translator\TranslatorInterface|null
     */
    public static function getDefaultTranslator()
    {
        return static::$defaultTranslator;
    }

    /**
     * Is there a default translation object set?
     *
     * @return bool
     */
    public static function hasDefaultTranslator()
    {
        return (bool) static::$defaultTranslator;
    }

    /**
     * Set default translation text domain for all validate objects
     *
     * @param  string $textDomain
     * @return void
     */
    public static function setDefaultTranslatorTextDomain($textDomain = 'default')
    {
        static::$defaultTranslatorTextDomain = $textDomain;
    }

    /**
     * Get default translation text domain for all validate objects
     *
     * @return string
     */
    public static function getDefaultTranslatorTextDomain()
    {
        return static::$defaultTranslatorTextDomain;
    }

    /**
     * Indicate whether or not translation should be enabled
     *
     * @param  bool $flag
     * @return AbstractValidator
     */
    public function setTranslatorEnabled($flag = true)
    {
        $this->abstractOptions['translatorEnabled'] = (bool) $flag;
        return $this;
    }

    /**
     * Is translation enabled?
     *
     * @return bool
     */
    public function isTranslatorEnabled()
    {
        return $this->abstractOptions['translatorEnabled'];
    }

    /**
     * Returns the maximum allowed message length
     *
     * @return int
     */
    public static function getMessageLength()
    {
        return static::$messageLength;
    }

    /**
     * Sets the maximum allowed message length
     *
     * @param int $length
     */
    public static function setMessageLength($length = -1)
    {
        static::$messageLength = $length;
    }

    /**
     * Translate a validation message
     *
     * @param  string $messageKey
     * @param  string $message
     * @return string
     */
    protected function translateMessage($messageKey, $message)
    {
        $translator = $this->getTranslator();
        if (!$translator) {
            return $message;
        }

        return $translator->translate($message, $this->getTranslatorTextDomain());
    }
}
