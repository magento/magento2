<?php

namespace OAuth\OAuth1\Signature\Exception;

use OAuth\Common\Exception\Exception;

/**
 * Thrown when an unsupported hash mechanism is requested in signature class.
 */
class UnsupportedHashAlgorithmException extends Exception
{
}
