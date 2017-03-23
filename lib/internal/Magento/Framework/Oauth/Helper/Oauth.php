<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Oauth\Helper;

class Oauth
{
    /**
     * #@+
     * Lengths of token fields
     */
    const LENGTH_TOKEN = 32;

    const LENGTH_TOKEN_SECRET = 32;

    const LENGTH_TOKEN_VERIFIER = 32;

    /**#@- */

    /**
     * #@+
     * Lengths of consumer fields
     */
    const LENGTH_CONSUMER_KEY = 32;

    const LENGTH_CONSUMER_SECRET = 32;

    /**#@- */

    /**
     * Nonce length
     */
    const LENGTH_NONCE = 32;

    /**
     * Value of callback URL when it is established or if the client is unable to receive callbacks
     *
     * @link http://tools.ietf.org/html/rfc5849#section-2.1     Requirement in RFC-5849
     */
    const CALLBACK_ESTABLISHED = 'oob';

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_mathRandom;

    /**
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(\Magento\Framework\Math\Random $mathRandom)
    {
        $this->_mathRandom = $mathRandom;
    }

    /**
     * Generate random string for token or secret or verifier
     *
     * @param int $length String length
     * @return string
     */
    public function generateRandomString($length)
    {
        return $this->_mathRandom->getRandomString(
            $length,
            \Magento\Framework\Math\Random::CHARS_DIGITS . \Magento\Framework\Math\Random::CHARS_LOWERS
        );
    }

    /**
     * Generate random string for token
     *
     * @return string
     */
    public function generateToken()
    {
        return $this->generateRandomString(self::LENGTH_TOKEN);
    }

    /**
     * Generate random string for token secret
     *
     * @return string
     */
    public function generateTokenSecret()
    {
        return $this->generateRandomString(self::LENGTH_TOKEN_SECRET);
    }

    /**
     * Generate random string for verifier
     *
     * @return string
     */
    public function generateVerifier()
    {
        return $this->generateRandomString(self::LENGTH_TOKEN_VERIFIER);
    }

    /**
     * Generate random string for consumer key
     *
     * @return string
     */
    public function generateConsumerKey()
    {
        return $this->generateRandomString(self::LENGTH_CONSUMER_KEY);
    }

    /**
     * Generate random string for consumer secret
     *
     * @return string
     */
    public function generateConsumerSecret()
    {
        return $this->generateRandomString(self::LENGTH_CONSUMER_SECRET);
    }
}
