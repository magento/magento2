<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Uri;

use Zend\Validator\EmailAddress as EmailValidator;
use Zend\Validator\ValidatorInterface;

/**
 * "Mailto" URI handler
 *
 * The 'mailto:...' scheme is loosely defined in RFC-1738
 */
class Mailto extends Uri
{
    protected static $validSchemes = array('mailto');

    /**
     * Validator for use when validating email address
     * @var ValidatorInterface
     */
    protected $emailValidator;

    /**
     * Check if the URI is a valid Mailto URI
     *
     * This applies additional specific validation rules beyond the ones
     * required by the generic URI syntax
     *
     * @return bool
     * @see    Uri::isValid()
     */
    public function isValid()
    {
        if ($this->host || $this->userInfo || $this->port) {
            return false;
        }

        if (empty($this->path)) {
            return false;
        }

        if (0 === strpos($this->path, '/')) {
            return false;
        }

        $validator = $this->getValidator();
        return $validator->isValid($this->path);
    }

    /**
     * Set the email address
     *
     * This is in fact equivalent to setPath() - but provides a more clear interface
     *
     * @param  string $email
     * @return Mailto
     */
    public function setEmail($email)
    {
        return $this->setPath($email);
    }

    /**
     * Get the email address
     *
     * This is infact equivalent to getPath() - but provides a more clear interface
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getPath();
    }

    /**
     * Set validator to use when validating email address
     *
     * @param  ValidatorInterface $validator
     * @return Mailto
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->emailValidator = $validator;
        return $this;
    }

    /**
     * Retrieve validator for use with validating email address
     *
     * If none is currently set, an EmailValidator instance with default options
     * will be used.
     *
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        if (null === $this->emailValidator) {
            $this->setValidator(new EmailValidator());
        }
        return $this->emailValidator;
    }
}
