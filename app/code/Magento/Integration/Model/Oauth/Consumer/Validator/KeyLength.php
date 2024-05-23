<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Oauth\Consumer\Validator;

use Laminas\Validator\StringLength;

/**
 * Validate OAuth keys
 */
class KeyLength extends StringLength
{
    /**
     * @var array
     */
    public $messages = [];

    /**
     * Default key name
     *
     * @var string
     */
    protected $_name = 'Key';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID   => "Invalid type given for %name%. String expected",
        self::TOO_SHORT => "%name% '%value%' is less than %min% characters long",
        self::TOO_LONG  => "%name% '%value%' is more than %max% characters long",
    ];

    /**
     * Additional variables available for validation failure messages
     *
     * @var array
     */
    protected $messageVariables = [
        'min'  => ['options' => 'min'],
        'max'  => ['options' => 'max'],
    ];

    /**
     * Sets KeyLength validator options
     *
     * Default encoding is set to utf-8 if none provided
     * New option name added to allow adding key name in validation error messages
     *
     * @inheritdoc
     */
    public function __construct($options = [])
    {
        if (!is_array($options)) {
            // phpcs:ignore
            $options = func_get_args();
            if (!isset($options[1])) {
                $options[1] = 'utf-8';
            }
            parent::__construct($options[0], $options[0], $options[1]);
            return;
        } else {
            if (isset($options['length'])) {
                $options['max'] = $options['min'] = $options['length'];
            }
            if (isset($options['name'])) {
                $this->_name = $options['name'];
            }
        }
        parent::__construct($options);
    }

    /**
     * Set length
     *
     * @param int|null $length
     * @return $this
     */
    public function setLength($length)
    {
        parent::setMax($length);
        parent::setMin($length);
        return $this;
    }

    /**
     * Set length
     *
     * @return int
     */
    public function getLength()
    {
        return parent::getMin();
    }

    /**
     * Defined by \Laminas\Validator\ValidatorInterface
     *
     * Returns true if and only if the string length of $value is at least the min option and
     * no greater than the max option (when the max option is not null).
     *
     * @param  string $value
     * @return boolean
     * @throws \Exception
     */
    public function isValid($value)
    {
        $result = parent::isValid($value);
        $messages = $this->getMessages();
        $newMessages = [];

        foreach ($messages as $key => $message) {
            $newMessages[$key] = str_replace('%name%', $this->getName(), $message);
        }
        $this->abstractOptions['messages'] = $newMessages;

        if (!$result && isset($newMessages[self::INVALID])) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception($newMessages[self::INVALID]);
        }
        return $result;
    }

    /**
     * Set key name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Get key name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
}
