<?php

namespace OAuth\Common\Token\Exception;

use OAuth\Common\Exception\Exception;

/**
 * Exception thrown when an expired token is attempted to be used.
 */
class ExpiredTokenException extends Exception
{
}
