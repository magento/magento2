<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Validator
 */

namespace Zend\Validator;

use Countable;

/**
 * @category   Zend
 * @package    Zend_Validator
 */
class ValidatorChain implements
    Countable,
    ValidatorInterface
{
    /**
     * @var ValidatorPluginManager
     */
    protected $plugins;

    /**
     * Validator chain
     *
     * @var array
     */
    protected $validators = array();

    /**
     * Array of validation failure messages
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Return the count of attached validators
     *
     * @return int
     */
    public function count()
    {
        return count($this->validators);
    }

    /**
     * Get plugin manager instance
     *
     * @return ValidatorPluginManager
     */
    public function getPluginManager()
    {
        if (!$this->plugins) {
            $this->setPluginManager(new ValidatorPluginManager());
        }
        return $this->plugins;
    }

    /**
     * Set plugin manager instance
     *
     * @param  ValidatorPluginManager $plugins Plugin manager
     * @return ValidatorChain
     */
    public function setPluginManager(ValidatorPluginManager $plugins)
    {
        $this->plugins = $plugins;
        return $this;
    }

    /**
     * Retrieve a validator by name
     *
     * @param  string     $name    Name of validator to return
     * @param  null|array $options Options to pass to validator constructor (if not already instantiated)
     * @return ValidatorInterface
     */
    public function plugin($name, array $options = null)
    {
        $plugins = $this->getPluginManager();
        return $plugins->get($name, $options);
    }

    /**
     * Adds a validator to the end of the chain
     *
     * If $breakChainOnFailure is true, then if the validator fails, the next validator in the chain,
     * if one exists, will not be executed.
     *
     * @param  ValidatorInterface      $validator
     * @param  boolean                 $breakChainOnFailure
     * @return ValidatorChain Provides a fluent interface
     */
    public function addValidator(ValidatorInterface $validator, $breakChainOnFailure = false)
    {
        $this->validators[] = array(
            'instance'            => $validator,
            'breakChainOnFailure' => (boolean)$breakChainOnFailure
        );
        return $this;
    }

    /**
     * Adds a validator to the beginning of the chain
     *
     * If $breakChainOnFailure is true, then if the validator fails, the next validator in the chain,
     * if one exists, will not be executed.
     *
     * @param  ValidatorInterface      $validator
     * @param  boolean                 $breakChainOnFailure
     * @return ValidatorChain Provides a fluent interface
     */
    public function prependValidator(ValidatorInterface $validator, $breakChainOnFailure = false)
    {
        array_unshift($this->validators,
                      array(
                           'instance'            => $validator,
                           'breakChainOnFailure' => (boolean)$breakChainOnFailure
                      )
        );
        return $this;
    }

    /**
     * Use the plugin manager to add a validator by name
     *
     * @param  string $name
     * @param  array  $options
     * @param  bool   $breakChainOnFailure
     * @return ValidatorChain
     */
    public function addByName($name, $options = array(), $breakChainOnFailure = false)
    {
        $validator = $this->plugin($name, $options);
        $this->addValidator($validator, $breakChainOnFailure);
        return $this;
    }

    /**
     * Use the plugin manager to prepend a validator by name
     *
     * @param  string $name
     * @param  array  $options
     * @param  bool   $breakChainOnFailure
     * @return ValidatorChain
     */
    public function prependByName($name, $options = array(), $breakChainOnFailure = false)
    {
        $validator = $this->plugin($name, $options);
        $this->prependValidator($validator, $breakChainOnFailure);
        return $this;
    }

    /**
     * Returns true if and only if $value passes all validations in the chain
     *
     * Validators are run in the order in which they were added to the chain (FIFO).
     *
     * @param  mixed $value
     * @param  mixed $context Extra "context" to provide the validator
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->messages = array();
        $result         = true;
        foreach ($this->validators as $element) {
            $validator = $element['instance'];
            if ($validator->isValid($value, $context)) {
                continue;
            }
            $result         = false;
            $messages       = $validator->getMessages();
            $this->messages = array_replace_recursive($this->messages, $messages);
            if ($element['breakChainOnFailure']) {
                break;
            }
        }
        return $result;
    }

    /**
     * Merge the validator chain with the one given in parameter
     *
     * @param ValidatorChain $validatorChain
     * @return ValidatorChain
     */
    public function merge(ValidatorChain $validatorChain)
    {
        foreach ($validatorChain->validators as $validator) {
            $this->validators[] = $validator;
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
        return $this->messages;
    }

    /**
     * Get all the validators
     *
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Invoke chain as command
     *
     * @param  mixed $value
     * @return boolean
     */
    public function __invoke($value)
    {
        return $this->isValid($value);
    }

    /**
     * Prepare validator chain for serialization
     *
     * Plugin manager (property 'plugins') cannot
     * be serialized. On wakeup the property remains unset
     * and next invokation to getPluginManager() sets
     * the default plugin manager instance (ValidatorPluginManager).
     *
     * @return array
     */
    public function __sleep()
    {
        return array('validators','messages');
    }
}
