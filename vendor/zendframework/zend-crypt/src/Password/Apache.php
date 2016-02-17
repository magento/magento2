<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\Password;

use Traversable;
use Zend\Math\Rand;
use Zend\Crypt\Utils;

/**
 * Apache password authentication
 *
 * @see http://httpd.apache.org/docs/2.2/misc/password_encryptions.html
 */
class Apache implements PasswordInterface
{
    const BASE64  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    const ALPHA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**
     * @var array
     */
    protected $supportedFormat = array(
        'crypt',
        'sha1',
        'md5',
        'digest',
    );

    /**
     * @var string
     */
    protected $format;

    /**
     * @var string AuthName (realm) for digest authentication
     */
    protected $authName;

    /**
     * @var string UserName
     */
    protected $userName;

    /**
     * Constructor
     *
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = array())
    {
        if (empty($options)) {
            return;
        }
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(
                'The options parameter must be an array or a Traversable'
            );
        }
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'format':
                    $this->setFormat($value);
                    break;
                case 'authname':
                    $this->setAuthName($value);
                    break;
                case 'username':
                    $this->setUserName($value);
                    break;
            }
        }
    }

    /**
     * Generate the hash of a password
     *
     * @param  string $password
     * @throws Exception\RuntimeException
     * @return string
     */
    public function create($password)
    {
        if (empty($this->format)) {
            throw new Exception\RuntimeException(
                'You must specify a password format'
            );
        }
        switch ($this->format) {
            case 'crypt':
                $hash = crypt($password, Rand::getString(2, self::ALPHA64));
                break;
            case 'sha1':
                $hash = '{SHA}' . base64_encode(sha1($password, true));
                break;
            case 'md5':
                $hash = $this->apr1Md5($password);
                break;
            case 'digest':
                if (empty($this->userName) || empty($this->authName)) {
                    throw new Exception\RuntimeException(
                        'You must specify UserName and AuthName (realm) to generate the digest'
                    );
                }
                $hash = md5($this->userName . ':' . $this->authName . ':' .$password);
                break;
        }

        return $hash;
    }

    /**
     * Verify if a password is correct against a hash value
     *
     * @param  string  $password
     * @param  string  $hash
     * @return bool
     */
    public function verify($password, $hash)
    {
        if (substr($hash, 0, 5) === '{SHA}') {
            $hash2 = '{SHA}' . base64_encode(sha1($password, true));
            return Utils::compareStrings($hash, $hash2);
        }

        if (substr($hash, 0, 6) === '$apr1$') {
            $token = explode('$', $hash);
            if (empty($token[2])) {
                throw new Exception\InvalidArgumentException(
                    'The APR1 password format is not valid'
                );
            }
            $hash2 = $this->apr1Md5($password, $token[2]);
            return Utils::compareStrings($hash, $hash2);
        }

        $bcryptPattern = '/\$2[ay]?\$[0-9]{2}\$[' . addcslashes(static::BASE64, '+/') . '\.]{53}/';

        if (strlen($hash) > 13 && ! preg_match($bcryptPattern, $hash)) { // digest
            if (empty($this->userName) || empty($this->authName)) {
                throw new Exception\RuntimeException(
                    'You must specify UserName and AuthName (realm) to verify the digest'
                );
            }
            $hash2 = md5($this->userName . ':' . $this->authName . ':' .$password);
            return Utils::compareStrings($hash, $hash2);
        }

        return Utils::compareStrings($hash, crypt($password, $hash));
    }

    /**
     * Set the format of the password
     *
     * @param  string $format
     * @throws Exception\InvalidArgumentException
     * @return Apache
     */
    public function setFormat($format)
    {
        $format = strtolower($format);
        if (!in_array($format, $this->supportedFormat)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The format %s specified is not valid. The supported formats are: %s',
                $format,
                implode(',', $this->supportedFormat)
            ));
        }
        $this->format = $format;

        return $this;
    }

    /**
     * Get the format of the password
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set the AuthName (for digest authentication)
     *
     * @param  string $name
     * @return Apache
     */
    public function setAuthName($name)
    {
        $this->authName = $name;

        return $this;
    }

    /**
     * Get the AuthName (for digest authentication)
     *
     * @return string
     */
    public function getAuthName()
    {
        return $this->authName;
    }

    /**
     * Set the username
     *
     * @param  string $name
     * @return Apache
     */
    public function setUserName($name)
    {
        $this->userName = $name;

        return $this;
    }

    /**
     * Get the username
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Convert a binary string using the alphabet "./0-9A-Za-z"
     *
     * @param  string $value
     * @return string
     */
    protected function toAlphabet64($value)
    {
        return strtr(strrev(substr(base64_encode($value), 2)), self::BASE64, self::ALPHA64);
    }

    /**
     * APR1 MD5 algorithm
     *
     * @param  string      $password
     * @param  null|string $salt
     * @return string
     */
    protected function apr1Md5($password, $salt = null)
    {
        if (null === $salt) {
            $salt = Rand::getString(8, self::ALPHA64);
        } else {
            if (strlen($salt) !== 8) {
                throw new Exception\InvalidArgumentException(
                    'The salt value for APR1 algorithm must be 8 characters long'
                );
            }
            for ($i = 0; $i < 8; $i++) {
                if (strpos(self::ALPHA64, $salt[$i]) === false) {
                    throw new Exception\InvalidArgumentException(
                        'The salt value must be a string in the alphabet "./0-9A-Za-z"'
                    );
                }
            }
        }
        $len  = strlen($password);
        $text = $password . '$apr1$' . $salt;
        $bin  = pack("H32", md5($password . $salt . $password));
        for ($i = $len; $i > 0; $i -= 16) {
            $text .= substr($bin, 0, min(16, $i));
        }
        for ($i = $len; $i > 0; $i >>= 1) {
            $text .= ($i & 1) ? chr(0) : $password[0];
        }
        $bin = pack("H32", md5($text));
        for ($i = 0; $i < 1000; $i++) {
            $new = ($i & 1) ? $password : $bin;
            if ($i % 3) {
                $new .= $salt;
            }
            if ($i % 7) {
                $new .= $password;
            }
            $new .= ($i & 1) ? $bin : $password;
            $bin = pack("H32", md5($new));
        }
        $tmp = '';
        for ($i = 0; $i < 5; $i++) {
            $k = $i + 6;
            $j = $i + 12;
            if ($j == 16) {
                $j = 5;
            }
            $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
        }
        $tmp = chr(0) . chr(0) . $bin[11] . $tmp;

        return '$apr1$' . $salt . '$' . $this->toAlphabet64($tmp);
    }
}
