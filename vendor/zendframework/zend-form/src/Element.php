<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\InitializableInterface;

class Element implements
    ElementAttributeRemovalInterface,
    ElementInterface,
    InitializableInterface,
    LabelAwareInterface
{
    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var null|string
     */
    protected $label;

    /**
     * @var array
     */
    protected $labelAttributes = array();

    /**
     * Label specific options
     *
     * @var array
     */
    protected $labelOptions = array();

    /**
     * @var array Validation error messages
     */
    protected $messages = array();

    /**
     * @var array custom options
     */
    protected $options = array();

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param  null|int|string  $name    Optional name for the element
     * @param  array            $options Optional options for the element
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($name = null, $options = array())
    {
        if (null !== $name) {
            $this->setName($name);
        }

        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * This function is automatically called when creating element with factory. It
     * allows to perform various operations (add elements...)
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Set value for name
     *
     * @param  string $name
     * @return Element|ElementInterface
     */
    public function setName($name)
    {
        $this->setAttribute('name', $name);
        return $this;
    }

    /**
     * Get value for name
     *
     * @return string|int
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * Set options for an element. Accepted options are:
     * - label: label to associate with the element
     * - label_attributes: attributes to use when the label is rendered
     * - label_options: label specific options
     *
     * @param  array|Traversable $options
     * @return Element|ElementInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'The options parameter must be an array or a Traversable'
            );
        }

        if (isset($options['label'])) {
            $this->setLabel($options['label']);
        }

        if (isset($options['label_attributes'])) {
            $this->setLabelAttributes($options['label_attributes']);
        }

        if (isset($options['label_options'])) {
            $this->setLabelOptions($options['label_options']);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get defined options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return the specified option
     *
     * @param string $option
     * @return NULL|mixed
     */
    public function getOption($option)
    {
        if (!isset($this->options[$option])) {
            return;
        }

        return $this->options[$option];
    }

    /**
     * Set a single option for an element
     *
     * @param  string $key
     * @param  mixed $value
     * @return self
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * Set a single element attribute
     *
     * @param  string $key
     * @param  mixed  $value
     * @return Element|ElementInterface
     */
    public function setAttribute($key, $value)
    {
        // Do not include the value in the list of attributes
        if ($key === 'value') {
            $this->setValue($value);
            return $this;
        }
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Retrieve a single element attribute
     *
     * @param  $key
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        if (!isset($this->attributes[$key])) {
            return;
        }

        return $this->attributes[$key];
    }

    /**
     * Remove a single attribute
     *
     * @param string $key
     * @return ElementInterface
     */
    public function removeAttribute($key)
    {
        unset($this->attributes[$key]);
        return $this;
    }

    /**
     * Does the element has a specific attribute ?
     *
     * @param  string $key
     * @return bool
     */
    public function hasAttribute($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Set many attributes at once
     *
     * Implementation will decide if this will overwrite or merge.
     *
     * @param  array|Traversable $arrayOrTraversable
     * @return Element|ElementInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setAttributes($arrayOrTraversable)
    {
        if (!is_array($arrayOrTraversable) && !$arrayOrTraversable instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable argument; received "%s"',
                __METHOD__,
                (is_object($arrayOrTraversable) ? get_class($arrayOrTraversable) : gettype($arrayOrTraversable))
            ));
        }
        foreach ($arrayOrTraversable as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Retrieve all attributes at once
     *
     * @return array|Traversable
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Remove many attributes at once
     *
     * @param array $keys
     * @return ElementInterface
     */
    public function removeAttributes(array $keys)
    {
        foreach ($keys as $key) {
            unset($this->attributes[$key]);
        }

        return $this;
    }

    /**
     * Clear all attributes
     *
     * @return Element|ElementInterface
     */
    public function clearAttributes()
    {
        $this->attributes = array();
        return $this;
    }

    /**
     * Set the element value
     *
     * @param  mixed $value
     * @return Element
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Retrieve the element value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the label used for this element
     *
     * @param $label
     * @return Element|ElementInterface
     */
    public function setLabel($label)
    {
        if (is_string($label)) {
            $this->label = $label;
        }

        return $this;
    }

    /**
     * Retrieve the label used for this element
     *
     * @return null|string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the attributes to use with the label
     *
     * @param array $labelAttributes
     * @return Element|ElementInterface
     */
    public function setLabelAttributes(array $labelAttributes)
    {
        $this->labelAttributes = $labelAttributes;
        return $this;
    }

    /**
     * Get the attributes to use with the label
     *
     * @return array
     */
    public function getLabelAttributes()
    {
        return $this->labelAttributes;
    }

    /**
     * Set many label options at once
     *
     * Implementation will decide if this will overwrite or merge.
     *
     * @param  array|Traversable $arrayOrTraversable
     * @return Element|ElementInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setLabelOptions($arrayOrTraversable)
    {
        if (!is_array($arrayOrTraversable) && !$arrayOrTraversable instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable argument; received "%s"',
                __METHOD__,
                (is_object($arrayOrTraversable) ? get_class($arrayOrTraversable) : gettype($arrayOrTraversable))
            ));
        }
        foreach ($arrayOrTraversable as $key => $value) {
            $this->setLabelOption($key, $value);
        }
        return $this;
    }

    /**
     * Get label specific options
     *
     * @return array
     */
    public function getLabelOptions()
    {
        return $this->labelOptions;
    }

    /**
     * Clear all label options
     *
     * @return Element|ElementInterface
     */
    public function clearLabelOptions()
    {
        $this->labelOptions = array();
        return $this;
    }

    /**
     * Remove many attributes at once
     *
     * @param array $keys
     * @return ElementInterface
     */
    public function removeLabelOptions(array $keys)
    {
        foreach ($keys as $key) {
            unset($this->labelOptions[$key]);
        }

        return $this;
    }

    /**
     * Set a single label optionn
     *
     * @param  string $key
     * @param  mixed  $value
     * @return Element|ElementInterface
     */
    public function setLabelOption($key, $value)
    {
        $this->labelOptions[$key] = $value;
        return $this;
    }

    /**
     * Retrieve a single label option
     *
     * @param  $key
     * @return mixed|null
     */
    public function getLabelOption($key)
    {
        if (!isset($this->labelOptions[$key])) {
            return;
        }

        return $this->labelOptions[$key];
    }

    /**
     * Remove a single label option
     *
     * @param string $key
     * @return ElementInterface
     */
    public function removeLabelOption($key)
    {
        unset($this->labelOptions[$key]);
        return $this;
    }

    /**
     * Does the element has a specific label option ?
     *
     * @param  string $key
     * @return bool
     */
    public function hasLabelOption($key)
    {
        return array_key_exists($key, $this->labelOptions);
    }

    /**
     * Set a list of messages to report when validation fails
     *
     * @param  array|Traversable $messages
     * @return Element|ElementInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setMessages($messages)
    {
        if (!is_array($messages) && !$messages instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable object of validation error messages; received "%s"',
                __METHOD__,
                (is_object($messages) ? get_class($messages) : gettype($messages))
            ));
        }

        $this->messages = $messages;
        return $this;
    }

    /**
     * Get validation error messages, if any.
     *
     * Returns a list of validation failure messages, if any.
     *
     * @return array|Traversable
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
