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

class CreditCard extends AbstractValidator
{
    /**
     * Detected CCI list
     *
     * @var string
     */
    const ALL              = 'All';
    const AMERICAN_EXPRESS = 'American_Express';
    const UNIONPAY         = 'Unionpay';
    const DINERS_CLUB      = 'Diners_Club';
    const DINERS_CLUB_US   = 'Diners_Club_US';
    const DISCOVER         = 'Discover';
    const JCB              = 'JCB';
    const LASER            = 'Laser';
    const MAESTRO          = 'Maestro';
    const MASTERCARD       = 'Mastercard';
    const SOLO             = 'Solo';
    const VISA             = 'Visa';

    const CHECKSUM       = 'creditcardChecksum';
    const CONTENT        = 'creditcardContent';
    const INVALID        = 'creditcardInvalid';
    const LENGTH         = 'creditcardLength';
    const PREFIX         = 'creditcardPrefix';
    const SERVICE        = 'creditcardService';
    const SERVICEFAILURE = 'creditcardServiceFailure';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::CHECKSUM       => "The input seems to contain an invalid checksum",
        self::CONTENT        => "The input must contain only digits",
        self::INVALID        => "Invalid type given. String expected",
        self::LENGTH         => "The input contains an invalid amount of digits",
        self::PREFIX         => "The input is not from an allowed institute",
        self::SERVICE        => "The input seems to be an invalid credit card number",
        self::SERVICEFAILURE => "An exception has been raised while validating the input",
    );

    /**
     * List of CCV names
     *
     * @var array
     */
    protected $cardName = array(
        0  => self::AMERICAN_EXPRESS,
        1  => self::DINERS_CLUB,
        2  => self::DINERS_CLUB_US,
        3  => self::DISCOVER,
        4  => self::JCB,
        5  => self::LASER,
        6  => self::MAESTRO,
        7  => self::MASTERCARD,
        8  => self::SOLO,
        9  => self::UNIONPAY,
        10 => self::VISA,
    );

    /**
     * List of allowed CCV lengths
     *
     * @var array
     */
    protected $cardLength = array(
        self::AMERICAN_EXPRESS => array(15),
        self::DINERS_CLUB      => array(14),
        self::DINERS_CLUB_US   => array(16),
        self::DISCOVER         => array(16),
        self::JCB              => array(15, 16),
        self::LASER            => array(16, 17, 18, 19),
        self::MAESTRO          => array(12, 13, 14, 15, 16, 17, 18, 19),
        self::MASTERCARD       => array(16),
        self::SOLO             => array(16, 18, 19),
        self::UNIONPAY         => array(16, 17, 18, 19),
        self::VISA             => array(16),
    );

    /**
     * List of accepted CCV provider tags
     *
     * @var array
     */
    protected $cardType = array(
        self::AMERICAN_EXPRESS => array('34', '37'),
        self::DINERS_CLUB      => array('300', '301', '302', '303', '304', '305', '36'),
        self::DINERS_CLUB_US   => array('54', '55'),
        self::DISCOVER         => array('6011', '622126', '622127', '622128', '622129', '62213',
                                        '62214', '62215', '62216', '62217', '62218', '62219',
                                        '6222', '6223', '6224', '6225', '6226', '6227', '6228',
                                        '62290', '62291', '622920', '622921', '622922', '622923',
                                        '622924', '622925', '644', '645', '646', '647', '648',
                                        '649', '65'),
        self::JCB              => array('1800', '2131', '3528', '3529', '353', '354', '355', '356', '357', '358'),
        self::LASER            => array('6304', '6706', '6771', '6709'),
        self::MAESTRO          => array('5018', '5020', '5038', '6304', '6759', '6761', '6762', '6763',
                                        '6764', '6765', '6766'),
        self::MASTERCARD       => array('51', '52', '53', '54', '55'),
        self::SOLO             => array('6334', '6767'),
        self::UNIONPAY         => array('622126', '622127', '622128', '622129', '62213', '62214',
                                        '62215', '62216', '62217', '62218', '62219', '6222', '6223',
                                        '6224', '6225', '6226', '6227', '6228', '62290', '62291',
                                        '622920', '622921', '622922', '622923', '622924', '622925'),
        self::VISA             => array('4'),
    );

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = array(
        'service' => null,     // Service callback for additional validation
        'type'    => array(),  // CCIs which are accepted by validation
    );

    /**
     * Constructor
     *
     * @param string|array|Traversable $options OPTIONAL Type of CCI to allow
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            $options = func_get_args();
            $temp['type'] = array_shift($options);
            if (!empty($options)) {
                $temp['service'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!array_key_exists('type', $options)) {
            $options['type'] = self::ALL;
        }

        $this->setType($options['type']);
        unset($options['type']);

        if (array_key_exists('service', $options)) {
            $this->setService($options['service']);
            unset($options['service']);
        }

        parent::__construct($options);
    }

    /**
     * Returns a list of accepted CCIs
     *
     * @return array
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * Sets CCIs which are accepted by validation
     *
     * @param  string|array $type Type to allow for validation
     * @return CreditCard Provides a fluid interface
     */
    public function setType($type)
    {
        $this->options['type'] = array();
        return $this->addType($type);
    }

    /**
     * Adds a CCI to be accepted by validation
     *
     * @param  string|array $type Type to allow for validation
     * @return CreditCard Provides a fluid interface
     */
    public function addType($type)
    {
        if (is_string($type)) {
            $type = array($type);
        }

        foreach ($type as $typ) {
            if (defined('self::' . strtoupper($typ)) && !in_array($typ, $this->options['type'])) {
                $this->options['type'][] = $typ;
            }

            if (($typ == self::ALL)) {
                $this->options['type'] = array_keys($this->cardLength);
            }
        }

        return $this;
    }

    /**
     * Returns the actual set service
     *
     * @return callable
     */
    public function getService()
    {
        return $this->options['service'];
    }

    /**
     * Sets a new callback for service validation
     *
     * @param  callable $service
     * @return CreditCard
     * @throws Exception\InvalidArgumentException on invalid service callback
     */
    public function setService($service)
    {
        if (!is_callable($service)) {
            throw new Exception\InvalidArgumentException('Invalid callback given');
        }

        $this->options['service'] = $service;
        return $this;
    }

    /**
     * Returns true if and only if $value follows the Luhn algorithm (mod-10 checksum)
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        if (!is_string($value)) {
            $this->error(self::INVALID, $value);
            return false;
        }

        if (!ctype_digit($value)) {
            $this->error(self::CONTENT, $value);
            return false;
        }

        $length = strlen($value);
        $types  = $this->getType();
        $foundp = false;
        $foundl = false;
        foreach ($types as $type) {
            foreach ($this->cardType[$type] as $prefix) {
                if (substr($value, 0, strlen($prefix)) == $prefix) {
                    $foundp = true;
                    if (in_array($length, $this->cardLength[$type])) {
                        $foundl = true;
                        break 2;
                    }
                }
            }
        }

        if ($foundp == false) {
            $this->error(self::PREFIX, $value);
            return false;
        }

        if ($foundl == false) {
            $this->error(self::LENGTH, $value);
            return false;
        }

        $sum    = 0;
        $weight = 2;

        for ($i = $length - 2; $i >= 0; $i--) {
            $digit = $weight * $value[$i];
            $sum += floor($digit / 10) + $digit % 10;
            $weight = $weight % 2 + 1;
        }

        if ((10 - $sum % 10) % 10 != $value[$length - 1]) {
            $this->error(self::CHECKSUM, $value);
            return false;
        }

        $service = $this->getService();
        if (!empty($service)) {
            try {
                $callback = new Callback($service);
                $callback->setOptions($this->getType());
                if (!$callback->isValid($value)) {
                    $this->error(self::SERVICE, $value);
                    return false;
                }
            } catch (\Exception $e) {
                $this->error(self::SERVICEFAILURE, $value);
                return false;
            }
        }

        return true;
    }
}
