<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator;

class EmailAddress extends AbstractValidator
{
    const INVALID            = 'emailAddressInvalid';
    const INVALID_FORMAT     = 'emailAddressInvalidFormat';
    const INVALID_HOSTNAME   = 'emailAddressInvalidHostname';
    const INVALID_MX_RECORD  = 'emailAddressInvalidMxRecord';
    const INVALID_SEGMENT    = 'emailAddressInvalidSegment';
    const DOT_ATOM           = 'emailAddressDotAtom';
    const QUOTED_STRING      = 'emailAddressQuotedString';
    const INVALID_LOCAL_PART = 'emailAddressInvalidLocalPart';
    const LENGTH_EXCEEDED    = 'emailAddressLengthExceeded';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID            => "Invalid type given. String expected",
        self::INVALID_FORMAT     => "The input is not a valid email address. Use the basic format local-part@hostname",
        self::INVALID_HOSTNAME   => "'%hostname%' is not a valid hostname for the email address",
        self::INVALID_MX_RECORD  => "'%hostname%' does not appear to have any valid MX or A records for the email address",
        self::INVALID_SEGMENT    => "'%hostname%' is not in a routable network segment. The email address should not be resolved from public network",
        self::DOT_ATOM           => "'%localPart%' can not be matched against dot-atom format",
        self::QUOTED_STRING      => "'%localPart%' can not be matched against quoted-string format",
        self::INVALID_LOCAL_PART => "'%localPart%' is not a valid local part for the email address",
        self::LENGTH_EXCEEDED    => "The input exceeds the allowed length",
    );

    /**
     * @var array
     */
    protected $messageVariables = array(
        'hostname'  => 'hostname',
        'localPart' => 'localPart'
    );

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $localPart;

    /**
     * Returns the found mx record informations
     *
     * @var array
     */
    protected $mxRecord;

    /**
     * Internal options array
     */
    protected $options = array(
        'useMxCheck'        => false,
        'useDeepMxCheck'    => false,
        'useDomainCheck'    => true,
        'allow'             => Hostname::ALLOW_DNS,
        'hostnameValidator' => null,
    );

    /**
     * Instantiates hostname validator for local use
     *
     * The following additional option keys are supported:
     * 'hostnameValidator' => A hostname validator, see Zend\Validator\Hostname
     * 'allow'             => Options for the hostname validator, see Zend\Validator\Hostname::ALLOW_*
     * 'useMxCheck'        => If MX check should be enabled, boolean
     * 'useDeepMxCheck'    => If a deep MX check should be done, boolean
     *
     * @param array|\Traversable $options OPTIONAL
     */
    public function __construct($options = array())
    {
        if (!is_array($options)) {
            $options = func_get_args();
            $temp['allow'] = array_shift($options);
            if (!empty($options)) {
                $temp['useMxCheck'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['hostnameValidator'] = array_shift($options);
            }

            $options = $temp;
        }

        parent::__construct($options);
    }

    /**
     * Sets the validation failure message template for a particular key
     * Adds the ability to set messages to the attached hostname validator
     *
     * @param  string $messageString
     * @param  string $messageKey     OPTIONAL
     * @return AbstractValidator Provides a fluent interface
     */
    public function setMessage($messageString, $messageKey = null)
    {
        if ($messageKey === null) {
            $this->getHostnameValidator()->setMessage($messageString);
            parent::setMessage($messageString);
            return $this;
        }

        if (!isset($this->messageTemplates[$messageKey])) {
            $this->getHostnameValidator()->setMessage($messageString, $messageKey);
        } else {
            parent::setMessage($messageString, $messageKey);
        }

        return $this;
    }

    /**
     * Returns the set hostname validator
     *
     * If was not previously set then lazy load a new one
     *
     * @return Hostname
     */
    public function getHostnameValidator()
    {
        if (!isset($this->options['hostnameValidator'])) {
            $this->options['hostnameValidator'] = new Hostname($this->getAllow());
        }

        return $this->options['hostnameValidator'];
    }

    /**
     * @param Hostname $hostnameValidator OPTIONAL
     * @return EmailAddress Provides a fluent interface
     */
    public function setHostnameValidator(Hostname $hostnameValidator = null)
    {
        $this->options['hostnameValidator'] = $hostnameValidator;

        return $this;
    }

    /**
     * Returns the allow option of the attached hostname validator
     *
     * @return int
     */
    public function getAllow()
    {
        return $this->options['allow'];
    }

    /**
     * Sets the allow option of the hostname validator to use
     *
     * @param int $allow
     * @return EmailAddress Provides a fluent interface
     */
    public function setAllow($allow)
    {
        $this->options['allow'] = $allow;
        if (isset($this->options['hostnameValidator'])) {
            $this->options['hostnameValidator']->setAllow($allow);
        }

        return $this;
    }

    /**
     * Whether MX checking via getmxrr is supported or not
     *
     * @return bool
     */
    public function isMxSupported()
    {
        return function_exists('getmxrr');
    }

    /**
     * Returns the set validateMx option
     *
     * @return bool
     */
    public function getMxCheck()
    {
        return $this->options['useMxCheck'];
    }

    /**
     * Set whether we check for a valid MX record via DNS
     *
     * This only applies when DNS hostnames are validated
     *
     * @param  bool $mx Set allowed to true to validate for MX records, and false to not validate them
     * @return EmailAddress Fluid Interface
     */
    public function useMxCheck($mx)
    {
        $this->options['useMxCheck'] = (bool) $mx;
        return $this;
    }

    /**
     * Returns the set deepMxCheck option
     *
     * @return bool
     */
    public function getDeepMxCheck()
    {
        return $this->options['useDeepMxCheck'];
    }

    /**
     * Use deep validation for MX records
     *
     * @param  bool $deep Set deep to true to perform a deep validation process for MX records
     * @return EmailAddress Fluid Interface
     */
    public function useDeepMxCheck($deep)
    {
        $this->options['useDeepMxCheck'] = (bool) $deep;
        return $this;
    }

    /**
     * Returns the set domainCheck option
     *
     * @return bool
     */
    public function getDomainCheck()
    {
        return $this->options['useDomainCheck'];
    }

    /**
     * Sets if the domain should also be checked
     * or only the local part of the email address
     *
     * @param  bool $domain
     * @return EmailAddress Fluid Interface
     */
    public function useDomainCheck($domain = true)
    {
        $this->options['useDomainCheck'] = (bool) $domain;
        return $this;
    }

    /**
     * Returns if the given host is reserved
     *
     * The following addresses are seen as reserved
     * '0.0.0.0/8', '10.0.0.0/8', '127.0.0.0/8'
     * '100.64.0.0/10'
     * '172.16.0.0/12'
     * '198.18.0.0/15'
     * '169.254.0.0/16', '192.168.0.0/16'
     * '192.0.2.0/24', '192.88.99.0/24', '198.51.100.0/24', '203.0.113.0/24'
     * '224.0.0.0/4', '240.0.0.0/4'
     * @see http://en.wikipedia.org/wiki/Reserved_IP_addresses
     *
     * As of RFC5753 (JAN 2010), the following blocks are no longer reserved:
     *   - 128.0.0.0/16
     *   - 191.255.0.0/16
     *   - 223.255.255.0/24
     * @see http://tools.ietf.org/html/rfc5735#page-6
     *
     * As of RFC6598 (APR 2012), the following blocks are now reserved:
     *   - 100.64.0.0/10
     * @see http://tools.ietf.org/html/rfc6598#section-7
     *
     * @param string $host
     * @return bool Returns false when minimal one of the given addresses is not reserved
     */
    protected function isReserved($host)
    {
        if (!preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $host)) {
            $host = gethostbynamel($host);
        } else {
            $host = array($host);
        }

        if (empty($host)) {
            return false;
        }

        foreach ($host as $server) {
            // Search for 0.0.0.0/8, 10.0.0.0/8, 127.0.0.0/8
            if (!preg_match('/^(0|10|127)(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){3}$/', $server) &&
                // Search for 100.64.0.0/10
                !preg_match('/^100\.(6[0-4]|[7-9][0-9]|1[0-1][0-9]|12[0-7])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server) &&
                // Search for 172.16.0.0/12
                !preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server) &&
                // Search for 198.18.0.0/15
                !preg_match('/^198\.(1[8-9])(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server) &&
                // Search for 169.254.0.0/16, 192.168.0.0/16
                !preg_match('/^(169\.254|192\.168)(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){2}$/', $server) &&
                // Search for 192.0.2.0/24, 192.88.99.0/24, 198.51.100.0/24, 203.0.113.0/24
                !preg_match('/^(192\.0\.2|192\.88\.99|198\.51\.100|203\.0\.113)\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))$/', $server) &&
                // Search for 224.0.0.0/4, 240.0.0.0/4
                !preg_match('/^(2(2[4-9]|[3-4][0-9]|5[0-5]))(\.([0-9]|[1-9][0-9]|1([0-9][0-9])|2([0-4][0-9]|5[0-5]))){3}$/', $server)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Internal method to validate the local part of the email address
     *
     * @return bool
     */
    protected function validateLocalPart()
    {
        // First try to match the local part on the common dot-atom format
        $result = false;

        // Dot-atom characters are: 1*atext *("." 1*atext)
        // atext: ALPHA / DIGIT / and "!", "#", "$", "%", "&", "'", "*",
        //        "+", "-", "/", "=", "?", "^", "_", "`", "{", "|", "}", "~"
        $atext = 'a-zA-Z0-9\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e';
        if (preg_match('/^[' . $atext . ']+(\x2e+[' . $atext . ']+)*$/', $this->idnToAscii($this->localPart))) {
            $result = true;
        } else {
            // Try quoted string format (RFC 5321 Chapter 4.1.2)

            // Quoted-string characters are: DQUOTE *(qtext/quoted-pair) DQUOTE
            $qtext      = '\x20-\x21\x23-\x5b\x5d-\x7e'; // %d32-33 / %d35-91 / %d93-126
            $quotedPair = '\x20-\x7e'; // %d92 %d32-126
            if (preg_match('/^"(['. $qtext .']|\x5c[' . $quotedPair . '])*"$/', $this->localPart)) {
                $result = true;
            } else {
                $this->error(self::DOT_ATOM);
                $this->error(self::QUOTED_STRING);
                $this->error(self::INVALID_LOCAL_PART);
            }
        }

        return $result;
    }

    /**
     * Returns the found MX Record information after validation including weight for further processing
     *
     * @return array
     */
    public function getMXRecord()
    {
        return $this->mxRecord;
    }

    /**
     * Internal method to validate the servers MX records
     *
     * @return bool
     */
    protected function validateMXRecords()
    {
        $mxHosts = array();
        $weight  = array();
        $result = getmxrr($this->idnToAscii($this->hostname), $mxHosts, $weight);
        if (!empty($mxHosts) && !empty($weight)) {
            $this->mxRecord = array_combine($mxHosts, $weight);
        } else {
            $this->mxRecord = $mxHosts;
        }

        arsort($this->mxRecord);

        // Fallback to IPv4 hosts if no MX record found (RFC 2821 SS 5).
        if (!$result) {
            $result = gethostbynamel($this->hostname);
            if (is_array($result)) {
                $this->mxRecord = array_flip($result);
            }
        }

        if (!$result) {
            $this->error(self::INVALID_MX_RECORD);
            return $result;
        }

        if (!$this->options['useDeepMxCheck']) {
            return $result;
        }

        $validAddress = false;
        $reserved     = true;
        foreach ($this->mxRecord as $hostname => $weight) {
            $res = $this->isReserved($hostname);
            if (!$res) {
                $reserved = false;
            }

            if (!$res
                && (checkdnsrr($hostname, "A")
                || checkdnsrr($hostname, "AAAA")
                || checkdnsrr($hostname, "A6"))
            ) {
                $validAddress = true;
                break;
            }
        }

        if (!$validAddress) {
            $result = false;
            $error  = ($reserved) ? self::INVALID_SEGMENT : self::INVALID_MX_RECORD;
            $this->error($error);
        }

        return $result;
    }

    /**
     * Internal method to validate the hostname part of the email address
     *
     * @return bool
     */
    protected function validateHostnamePart()
    {
        $hostname = $this->getHostnameValidator()->setTranslator($this->getTranslator())
                         ->isValid($this->hostname);
        if (!$hostname) {
            $this->error(self::INVALID_HOSTNAME);
            // Get messages and errors from hostnameValidator
            foreach ($this->getHostnameValidator()->getMessages() as $code => $message) {
                $this->abstractOptions['messages'][$code] = $message;
            }
        } elseif ($this->options['useMxCheck']) {
            // MX check on hostname
            $hostname = $this->validateMXRecords();
        }

        return $hostname;
    }

    /**
     * Splits the given value in hostname and local part of the email address
     *
     * @param string $value Email address to be split
     * @return bool Returns false when the email can not be split
     */
    protected function splitEmailParts($value)
    {
        $value = is_string($value) ? $value : '';

        // Split email address up and disallow '..'
        if (strpos($value, '..') !== false
            || ! preg_match('/^(.+)@([^@]+)$/', $value, $matches)
        ) {
            return false;
        }

        $this->localPart = $matches[1];
        $this->hostname  = $matches[2];

        return true;
    }

    /**
     * Defined by Zend\Validator\ValidatorInterface
     *
     * Returns true if and only if $value is a valid email address
     * according to RFC2822
     *
     * @link   http://www.ietf.org/rfc/rfc2822.txt RFC2822
     * @link   http://www.columbia.edu/kermit/ascii.html US-ASCII characters
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $length  = true;
        $this->setValue($this->idnToUtf8($value));

        // Split email address up and disallow '..'
        if (!$this->splitEmailParts($this->getValue())) {
            $this->error(self::INVALID_FORMAT);
            return false;
        }

        if ((strlen($this->localPart) > 64) || (strlen($this->hostname) > 255)) {
            $length = false;
            $this->error(self::LENGTH_EXCEEDED);
        }

        // Match hostname part
        if ($this->options['useDomainCheck']) {
            $hostname = $this->validateHostnamePart();
        }

        $local = $this->validateLocalPart();

        // If both parts valid, return true
        if ($local && $length) {
            if (($this->options['useDomainCheck'] && $hostname) || !$this->options['useDomainCheck']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Safely convert UTF-8 encoded domain name to ASCII
     * @param string $email  the UTF-8 encoded email
     * @return string
     */
    protected function idnToAscii($email)
    {
        if (extension_loaded('intl')) {
            return (idn_to_ascii($email) ?: $email);
        }
        return $email;
    }

    /**
     * Safely convert ASCII encoded domain name to UTF-8
     * @param string $email the ASCII encoded email
     * @return string
     */
    protected function idnToUtf8($email)
    {
        if (extension_loaded('intl')) {
            return idn_to_utf8($email);
        }
        return $email;
    }
}
