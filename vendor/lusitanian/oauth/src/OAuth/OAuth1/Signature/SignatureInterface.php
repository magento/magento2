<?php

namespace OAuth\OAuth1\Signature;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Uri\UriInterface;

interface SignatureInterface
{
    /**
     * @param string $algorithm
     */
    public function setHashingAlgorithm($algorithm);

    /**
     * @param string $token
     */
    public function setTokenSecret($token);

    /**
     * @param UriInterface $uri
     * @param array        $params
     * @param string       $method
     *
     * @return string
     */
    public function getSignature(UriInterface $uri, array $params, $method = 'POST');
}
