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

use Traversable;
use Zend\Math\Rand;
use Zend\Session\Container as SessionContainer;
use Zend\Stdlib\ArrayUtils;

class Csrf extends AbstractValidator
{
    /**
     * Error codes
     * @const string
     */
    const NOT_SAME = 'notSame';

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_SAME => "The form submitted did not originate from the expected site",
    );

    /**
     * Actual hash used.
     *
     * @var mixed
     */
    protected $hash;

    /**
     * Static cache of the session names to generated hashes
     *
     * @var array
     */
    protected static $hashCache;

    /**
     * Name of CSRF element (used to create non-colliding hashes)
     *
     * @var string
     */
    protected $name = 'csrf';

    /**
     * Salt for CSRF token
     * @var string
     */
    protected $salt = 'salt';

    /**
     * @var SessionContainer
     */
    protected $session;

    /**
     * TTL for CSRF token
     * @var int|null
     */
    protected $timeout = 300;

    /**
     * Constructor
     *
     * @param  array|Traversable $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            $options = (array) $options;
        }

        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'name':
                    $this->setName($value);
                    break;
                case 'salt':
                    $this->setSalt($value);
                    break;
                case 'session':
                    $this->setSession($value);
                    break;
                case 'timeout':
                    $this->setTimeout($value);
                    break;
                default:
                    // ignore unknown options
                    break;
            }
        }
    }

    /**
     * Does the provided token match the one generated?
     *
     * @param  string $value
     * @param  mixed $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue((string) $value);

        $hash = $this->getValidationToken();

        if ($value !== $hash) {
            $this->error(self::NOT_SAME);
            return false;
        }

        return true;
    }

    /**
     * Set CSRF name
     *
     * @param  string $name
     * @return Csrf
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Get CSRF name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set session container
     *
     * @param  SessionContainer $session
     * @return Csrf
     */
    public function setSession(SessionContainer $session)
    {
        $this->session = $session;
        if ($this->hash) {
            $this->initCsrfToken();
        }
        return $this;
    }

    /**
     * Get session container
     *
     * Instantiate session container if none currently exists
     *
     * @return SessionContainer
     */
    public function getSession()
    {
        if (null === $this->session) {
            $this->session = new SessionContainer($this->getSessionName());
        }
        return $this->session;
    }

    /**
     * Salt for CSRF token
     *
     * @param  string $salt
     * @return Csrf
     */
    public function setSalt($salt)
    {
        $this->salt = (string) $salt;
        return $this;
    }

    /**
     * Retrieve salt for CSRF token
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Retrieve CSRF token
     *
     * If no CSRF token currently exists, or should be regenerated,
     * generates one.
     *
     * @param  bool $regenerate    default false
     * @return string
     */
    public function getHash($regenerate = false)
    {
        if ((null === $this->hash) || $regenerate) {
            if ($regenerate) {
                $this->hash = null;
            } else {
                $this->hash = $this->getValidationToken();
            }
            if (null === $this->hash) {
                $this->generateHash();
            }
        }
        return $this->hash;
    }

    /**
     * Get session namespace for CSRF token
     *
     * Generates a session namespace based on salt, element name, and class.
     *
     * @return string
     */
    public function getSessionName()
    {
        return str_replace('\\', '_', __CLASS__) . '_'
            . $this->getSalt() . '_'
            . strtr($this->getName(), array('[' => '_', ']' => ''));
    }

    /**
     * Set timeout for CSRF session token
     *
     * @param  int|null $ttl
     * @return Csrf
     */
    public function setTimeout($ttl)
    {
        $this->timeout = ($ttl !== null) ? (int)$ttl : null;
        return $this;
    }

    /**
     * Get CSRF session token timeout
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Initialize CSRF token in session
     *
     * @return void
     */
    protected function initCsrfToken()
    {
        $session = $this->getSession();
        //$session->setExpirationHops(1, null, true);
        $timeout = $this->getTimeout();
        if (null !== $timeout) {
            $session->setExpirationSeconds($timeout);
        }
        $session->hash = $this->getHash();
    }

    /**
     * Generate CSRF token
     *
     * Generates CSRF token and stores both in {@link $hash} and element
     * value.
     *
     * @return void
     */
    protected function generateHash()
    {
        if (isset(static::$hashCache[$this->getSessionName()])) {
            $this->hash = static::$hashCache[$this->getSessionName()];
        } else {
            $this->hash = md5($this->getSalt() . Rand::getBytes(32) .  $this->getName());
            static::$hashCache[$this->getSessionName()] = $this->hash;
        }
        $this->setValue($this->hash);
        $this->initCsrfToken();
    }

    /**
     * Get validation token
     *
     * Retrieve token from session, if it exists.
     *
     * @return null|string
     */
    protected function getValidationToken()
    {
        $session = $this->getSession();
        if (isset($session->hash)) {
            return $session->hash;
        }
        return null;
    }
}
